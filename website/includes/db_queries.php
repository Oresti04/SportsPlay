<?php
/**
 * db_queries.php — Live database queries for coach / parent / player dashboards.
 * Replaces role_demo_data.php with real data from the sportsplay DB.
 */

if (!function_exists('sp_coach_data')) {

    /**
     * Fetch all live data for the logged-in coach.
     */
    function sp_coach_data(PDO $pdo, int $userId): array
    {
        // ── Team(s) this coach is assigned to ──
        $stmt = $pdo->prepare("
            SELECT t.team_id, t.name, t.season, t.sport, t.city,
                   l.name AS league_name, l.season AS league_season, l.sport AS league_sport
            FROM team_coaches tc
            JOIN teams t ON t.team_id = tc.team_id
            JOIN leagues l ON l.league_id = t.league_id
            WHERE tc.user_id = :uid
            ORDER BY (tc.role = 'head') DESC, t.team_id DESC
            LIMIT 1
        ");
        $stmt->execute(['uid' => $userId]);
        $teamRow = $stmt->fetch();

        if (!$teamRow) {
            return [
                'team' => ['team_id' => 0, 'name' => 'No team assigned', 'season' => '—', 'sport' => '—',
                           'league' => '—', 'home_field' => '—', 'assistant' => '—',
                           'record' => '0W · 0D · 0L', 'training_days' => '—'],
                'players' => [],
                'parents' => [],
                'schedule' => [],
                'conversations' => [],
                'announcements' => [],
            ];
        }

        $teamId = (int)$teamRow['team_id'];

        // ── Assistant coach ──
        $stmt = $pdo->prepare("
            SELECT u.first_name, u.last_name
            FROM team_coaches tc JOIN users u ON u.user_id = tc.user_id
            WHERE tc.team_id = :tid AND tc.user_id != :uid
            LIMIT 1
        ");
        $stmt->execute(['tid' => $teamId, 'uid' => $userId]);
        $assistant = $stmt->fetch();
        $assistantName = $assistant ? trim($assistant['first_name'] . ' ' . $assistant['last_name']) : '—';

        // ── Training days (distinct day-of-week from upcoming trainings) ──
        $stmt = $pdo->prepare("
            SELECT d FROM (
                SELECT DAYNAME(training_datetime) AS d, DAYOFWEEK(training_datetime) AS dw
                FROM trainings
                WHERE team_id = :tid AND training_datetime >= CURDATE()
                GROUP BY d, dw
                ORDER BY dw
                LIMIT 4
            ) AS sub
        ");
        $stmt->execute(['tid' => $teamId]);
        $trainingDays = array_column($stmt->fetchAll(), 'd');
        $trainingDaysStr = !empty($trainingDays) ? implode(' / ', array_map(fn($d) => substr($d, 0, 3), $trainingDays)) : '—';

        // ── Record (W/D/L from completed matches) ──
        $stmt = $pdo->prepare("
            SELECT
                SUM(CASE
                    WHEN (home_team_id = :t1 AND home_score > away_score) OR (away_team_id = :t2 AND away_score > home_score) THEN 1 ELSE 0 END) AS wins,
                SUM(CASE
                    WHEN home_score = away_score THEN 1 ELSE 0 END) AS draws,
                SUM(CASE
                    WHEN (home_team_id = :t3 AND home_score < away_score) OR (away_team_id = :t4 AND away_score < home_score) THEN 1 ELSE 0 END) AS losses
            FROM matches
            WHERE status = 'completed' AND (home_team_id = :t5 OR away_team_id = :t6)
        ");
        $stmt->execute(['t1'=>$teamId,'t2'=>$teamId,'t3'=>$teamId,'t4'=>$teamId,'t5'=>$teamId,'t6'=>$teamId]);
        $rec = $stmt->fetch();
        $record = ((int)($rec['wins']??0)).'W · '.((int)($rec['draws']??0)).'D · '.((int)($rec['losses']??0)).'L';

        // ── Home field (most common training location) ──
        $stmt = $pdo->prepare("
            SELECT location, COUNT(*) AS cnt FROM trainings
            WHERE team_id = :tid AND location IS NOT NULL
            GROUP BY location ORDER BY cnt DESC LIMIT 1
        ");
        $stmt->execute(['tid' => $teamId]);
        $hf = $stmt->fetch();
        $homeField = $hf ? $hf['location'] : '—';

        $team = [
            'team_id'       => $teamId,
            'name'          => $teamRow['name'],
            'season'        => $teamRow['season'] ?: $teamRow['league_season'],
            'sport'         => $teamRow['sport'] ?: $teamRow['league_sport'],
            'league'        => $teamRow['league_name'] . ' · ' . ($teamRow['league_season'] ?: ''),
            'home_field'    => $homeField,
            'assistant'     => $assistantName,
            'record'        => $record,
            'training_days' => $trainingDaysStr,
        ];

        // ── Players on this team ──
        $stmt = $pdo->prepare("
            SELECT p.player_id, p.jersey_number, p.position, p.dob,
                   u.first_name, u.last_name, u.email,
                   p.guardian_name, p.guardian_phone
            FROM players p
            JOIN users u ON u.user_id = p.user_id
            WHERE p.team_id = :tid
            ORDER BY p.jersey_number
        ");
        $stmt->execute(['tid' => $teamId]);
        $playersRaw = $stmt->fetchAll();

        $players = [];
        foreach ($playersRaw as $pr) {
            $age = $pr['dob'] ? (int)date_diff(date_create($pr['dob']), date_create('now'))->y : '—';
            $players[] = [
                'player_id' => (int)$pr['player_id'],
                'number'    => (int)$pr['jersey_number'],
                'name'      => trim($pr['first_name'] . ' ' . $pr['last_name']),
                'pos'       => $pr['position'] ?: '—',
                'age'       => $age,
                'parent'    => $pr['guardian_name'] ?: '—',
                'phone'     => $pr['guardian_phone'] ?: '—',
            ];
        }

        // ── Parent contacts (from parent_players join) ──
        $playerIds = array_column($playersRaw, 'player_id');
        $parents = [];
        if (!empty($playerIds)) {
            $placeholders = implode(',', array_fill(0, count($playerIds), '?'));
            $stmt = $pdo->prepare("
                SELECT DISTINCT pa.parent_id, pu.first_name AS pfn, pu.last_name AS pln, pu.email AS pemail,
                       pa.phone AS pphone,
                       cu.first_name AS cfn, cu.last_name AS cln
                FROM parent_players pp
                JOIN parents pa ON pa.parent_id = pp.parent_id
                JOIN users pu ON pu.user_id = pa.user_id
                JOIN players pl ON pl.player_id = pp.player_id
                JOIN users cu ON cu.user_id = pl.user_id
                WHERE pp.player_id IN ($placeholders)
            ");
            $stmt->execute($playerIds);
            foreach ($stmt->fetchAll() as $row) {
                $parents[] = [
                    'parent' => trim($row['pfn'] . ' ' . $row['pln']),
                    'child'  => trim($row['cfn'] . ' ' . $row['cln']),
                    'email'  => $row['pemail'],
                    'phone'  => $row['pphone'] ?: '—',
                ];
            }
        }

        // ── Upcoming schedule (trainings + matches merged, next 14 days) ──
        $schedule = [];

        $stmt = $pdo->prepare("
            SELECT 'Training' AS kind, COALESCE(notes, 'Training Session') AS title,
                   training_datetime AS dt, location
            FROM trainings
            WHERE team_id = :tid AND training_datetime >= NOW()
            ORDER BY training_datetime LIMIT 10
        ");
        $stmt->execute(['tid' => $teamId]);
        foreach ($stmt->fetchAll() as $r) {
            $schedule[] = [
                'kind'     => 'Training',
                'title'    => $r['title'],
                'date'     => date('D, M j', strtotime($r['dt'])),
                'time'     => date('g:i A', strtotime($r['dt'])),
                'location' => $r['location'] ?: '—',
                'sort'     => $r['dt'],
            ];
        }

        $stmt = $pdo->prepare("
            SELECT m.match_datetime AS dt, m.location,
                   ht.name AS home_name, at.name AS away_name,
                   m.home_team_id, m.away_team_id
            FROM matches m
            JOIN teams ht ON ht.team_id = m.home_team_id
            JOIN teams at ON at.team_id = m.away_team_id
            WHERE m.status = 'scheduled'
              AND (m.home_team_id = :t1 OR m.away_team_id = :t2)
              AND m.match_datetime >= NOW()
            ORDER BY m.match_datetime LIMIT 10
        ");
        $stmt->execute(['t1' => $teamId, 't2' => $teamId]);
        foreach ($stmt->fetchAll() as $r) {
            $opponent = ((int)$r['home_team_id'] === $teamId) ? $r['away_name'] : $r['home_name'];
            $schedule[] = [
                'kind'     => 'Match',
                'title'    => 'vs ' . $opponent,
                'date'     => date('D, M j', strtotime($r['dt'])),
                'time'     => date('g:i A', strtotime($r['dt'])),
                'location' => $r['location'] ?: 'TBD',
                'sort'     => $r['dt'],
            ];
        }
        usort($schedule, fn($a, $b) => $a['sort'] <=> $b['sort']);

        // ── Announcements (from announcements table if exists) ──
        $announcements = [];
        try {
            $stmt = $pdo->prepare("
                SELECT title, body, audience, created_at
                FROM announcements
                WHERE team_id = :tid
                ORDER BY created_at DESC LIMIT 10
            ");
            $stmt->execute(['tid' => $teamId]);
            foreach ($stmt->fetchAll() as $a) {
                $announcements[] = [
                    'title'    => $a['title'],
                    'body'     => $a['body'],
                    'audience' => $a['audience'],
                    'time'     => date('M j, Y · g:i A', strtotime($a['created_at'])),
                ];
            }
        } catch (Throwable $e) {
            // announcements table might not exist yet
        }

        return compact('team', 'players', 'parents', 'schedule', 'announcements');
    }

    /**
     * Fetch all live data for the logged-in parent.
     */
    function sp_parent_data(PDO $pdo, int $userId): array
    {
        // ── Get parent record ──
        $stmt = $pdo->prepare("SELECT parent_id, phone, balance_cents FROM parents WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $parentRow = $stmt->fetch();

        $children = [];
        $schedule = [];
        $selected = ['name'=>'—','team'=>'—','position'=>'—','jersey'=>0,'coach'=>'—','coach_email'=>'—'];

        if ($parentRow) {
            $parentId = (int)$parentRow['parent_id'];

            // ── Children linked to this parent ──
            $stmt = $pdo->prepare("
                SELECT pl.player_id, pl.jersey_number, pl.position, pl.dob,
                       u.first_name, u.last_name,
                       t.team_id, t.name AS team_name, t.season, t.sport
                FROM parent_players pp
                JOIN players pl ON pl.player_id = pp.player_id
                JOIN users u ON u.user_id = pl.user_id
                JOIN teams t ON t.team_id = pl.team_id
                WHERE pp.parent_id = :pid
            ");
            $stmt->execute(['pid' => $parentId]);
            $childRows = $stmt->fetchAll();

            foreach ($childRows as $c) {
                $age = $c['dob'] ? (int)date_diff(date_create($c['dob']), date_create('now'))->y : '—';
                $children[] = [
                    'name'      => trim($c['first_name'] . ' ' . $c['last_name']),
                    'age'       => $age,
                    'team'      => $c['team_name'],
                    'position'  => $c['position'] ?: '—',
                    'jersey'    => (int)$c['jersey_number'],
                    'team_id'   => (int)$c['team_id'],
                    'player_id' => (int)$c['player_id'],
                ];
            }

            if (!empty($children)) {
                $sel = $children[0];
                $teamId = $sel['team_id'];

                // Get coach for this team
                $stmt = $pdo->prepare("
                    SELECT u.first_name, u.last_name, u.email, u.phone
                    FROM team_coaches tc JOIN users u ON u.user_id = tc.user_id
                    WHERE tc.team_id = :tid LIMIT 1
                ");
                $stmt->execute(['tid' => $teamId]);
                $coachRow = $stmt->fetch();

                $selected = [
                    'name'        => $sel['name'],
                    'team'        => $sel['team'],
                    'position'    => $sel['position'],
                    'jersey'      => $sel['jersey'],
                    'coach'       => $coachRow ? trim($coachRow['first_name'] . ' ' . $coachRow['last_name']) : '—',
                    'coach_email' => $coachRow['email'] ?? '—',
                ];

                // Schedule for first child's team
                $schedule = sp_team_schedule($pdo, $teamId);
            }
        }

        // ── Payments from payments table ──
        $payments = [];
        try {
            $stmt = $pdo->prepare("
                SELECT item, amount_cents, status, due_date
                FROM payments WHERE parent_id = :pid
                ORDER BY created_at DESC
            ");
            $stmt->execute(['pid' => $parentRow['parent_id'] ?? 0]);
            foreach ($stmt->fetchAll() as $p) {
                $payments[] = [
                    'item'   => $p['item'],
                    'amount' => '$' . number_format($p['amount_cents'] / 100, 2),
                    'status' => ucfirst($p['status']),
                    'date'   => $p['due_date'] ? date('M j, Y', strtotime($p['due_date'])) : '—',
                ];
            }
        } catch (Throwable $e) {
            // payments table might not exist yet
        }

        // ── Announcements for parent's child team ──
        $announcements = [];
        if (!empty($children)) {
            try {
                $stmt = $pdo->prepare("
                    SELECT title, body, created_at FROM announcements
                    WHERE team_id = :tid ORDER BY created_at DESC LIMIT 5
                ");
                $stmt->execute(['tid' => $children[0]['team_id']]);
                foreach ($stmt->fetchAll() as $a) {
                    $announcements[] = [
                        'title' => $a['title'],
                        'body'  => $a['body'],
                        'time'  => date('M j · g:i A', strtotime($a['created_at'])),
                    ];
                }
            } catch (Throwable $e) {}
        }

        return compact('children', 'selected', 'schedule', 'payments', 'announcements');
    }

    /**
     * Fetch all live data for the logged-in player.
     */
    function sp_player_data(PDO $pdo, int $userId): array
    {
        $stmt = $pdo->prepare("
            SELECT p.player_id, p.jersey_number, p.position, p.dob, p.team_id,
                   t.name AS team_name, t.season, t.sport,
                   l.league_id, l.name AS league_name
            FROM players p
            JOIN teams t ON t.team_id = p.team_id
            JOIN leagues l ON l.league_id = t.league_id
            WHERE p.user_id = :uid
            LIMIT 1
        ");
        $stmt->execute(['uid' => $userId]);
        $playerRow = $stmt->fetch();

        if (!$playerRow) {
            return [
                'profile'  => ['name'=>'—','team'=>'—','position'=>'—','number'=>0,'season'=>'—'],
                'stats'    => ['matches'=>0,'goals'=>0,'assists'=>0,'attendance'=>'—'],
                'schedule' => [],
                'standings' => [],
                'payments' => [],
                'announcements' => [],
            ];
        }

        $teamId = (int)$playerRow['team_id'];
        $leagueId = (int)$playerRow['league_id'];

        // ── Profile ──
        $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        $u = $stmt->fetch();
        $playerName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));

        $profile = [
            'name'     => $playerName,
            'team'     => $playerRow['team_name'],
            'position' => $playerRow['position'] ?: '—',
            'number'   => (int)$playerRow['jersey_number'],
            'season'   => $playerRow['season'] ?: '—',
        ];

        // ── Stats (from completed matches — simplified count) ──
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS matches_played FROM matches
            WHERE status = 'completed' AND (home_team_id = :t1 OR away_team_id = :t2)
        ");
        $stmt->execute(['t1' => $teamId, 't2' => $teamId]);
        $mp = (int)$stmt->fetchColumn();

        // Attendance
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS total FROM training_attendance
            WHERE player_id = :pid AND status IN ('present','late')
        ");
        $stmt->execute(['pid' => (int)$playerRow['player_id']]);
        $attended = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM trainings WHERE team_id = :tid");
        $stmt->execute(['tid' => $teamId]);
        $totalTrainings = (int)$stmt->fetchColumn();
        $attendancePct = $totalTrainings > 0 ? round(($attended / $totalTrainings) * 100) . '%' : '—';

        $stats = [
            'matches'    => $mp,
            'goals'      => 0,
            'assists'    => 0,
            'attendance' => $attendancePct,
        ];

        // ── Schedule ──
        $schedule = sp_team_schedule($pdo, $teamId);

        // ── Standings (teams in same league) ──
        $standings = [];
        $stmt = $pdo->prepare("
            SELECT t.team_id, t.name FROM teams t WHERE t.league_id = :lid AND t.is_active = 1
        ");
        $stmt->execute(['lid' => $leagueId]);
        $leagueTeams = $stmt->fetchAll();

        foreach ($leagueTeams as $lt) {
            $tid = (int)$lt['team_id'];
            $stmt2 = $pdo->prepare("
                SELECT
                    SUM(CASE WHEN (home_team_id=:t1 AND home_score>away_score) OR (away_team_id=:t2 AND away_score>home_score) THEN 1 ELSE 0 END) AS w,
                    SUM(CASE WHEN home_score=away_score THEN 1 ELSE 0 END) AS d,
                    SUM(CASE WHEN (home_team_id=:t3 AND home_score<away_score) OR (away_team_id=:t4 AND away_score<home_score) THEN 1 ELSE 0 END) AS l
                FROM matches WHERE status='completed' AND (home_team_id=:t5 OR away_team_id=:t6)
            ");
            $stmt2->execute(['t1'=>$tid,'t2'=>$tid,'t3'=>$tid,'t4'=>$tid,'t5'=>$tid,'t6'=>$tid]);
            $r = $stmt2->fetch();
            $w=(int)($r['w']??0); $d2=(int)($r['d']??0); $l2=(int)($r['l']??0);
            $standings[] = [
                'team' => $lt['name'],
                'pts'  => $w * 3 + $d2,
                'w'    => $w, 'd' => $d2, 'l' => $l2,
            ];
        }
        usort($standings, fn($a, $b) => $b['pts'] <=> $a['pts']);

        // ── Payments ──
        $payments = [];
        try {
            $stmt = $pdo->prepare("
                SELECT py.item, py.amount_cents, py.status, py.due_date
                FROM payments py
                JOIN parent_players pp ON pp.parent_id = py.parent_id
                WHERE pp.player_id = :pid
                ORDER BY py.created_at DESC
            ");
            $stmt->execute(['pid' => (int)$playerRow['player_id']]);
            foreach ($stmt->fetchAll() as $p) {
                $payments[] = [
                    'item'   => $p['item'],
                    'amount' => '$' . number_format($p['amount_cents'] / 100, 2),
                    'status' => ucfirst($p['status']),
                    'date'   => $p['due_date'] ? date('M j, Y', strtotime($p['due_date'])) : '—',
                ];
            }
        } catch (Throwable $e) {}

        // ── Announcements ──
        $announcements = [];
            try {
                $stmt = $pdo->prepare("
                    SELECT title, body, created_at FROM announcements
                    WHERE team_id = :tid
                      AND audience IN ('all', 'players')
                    ORDER BY created_at DESC LIMIT 5
                ");
                $stmt->execute(['tid' => $teamId]);
            foreach ($stmt->fetchAll() as $a) {
                $announcements[] = [
                    'title' => $a['title'],
                    'body'  => $a['body'],
                    'time'  => date('M j · g:i A', strtotime($a['created_at'])),
                ];
            }
        } catch (Throwable $e) {}

        return compact('profile', 'stats', 'schedule', 'standings', 'payments', 'announcements');
    }

    /**
     * Helper: get merged schedule for a team.
     */
    function sp_team_schedule(PDO $pdo, int $teamId): array
    {
        $schedule = [];

        $stmt = $pdo->prepare("
            SELECT 'Training' AS kind, COALESCE(notes, 'Training Session') AS title,
                   training_datetime AS dt, location
            FROM trainings
            WHERE team_id = :tid AND training_datetime >= NOW()
            ORDER BY training_datetime LIMIT 10
        ");
        $stmt->execute(['tid' => $teamId]);
        foreach ($stmt->fetchAll() as $r) {
            $schedule[] = [
                'kind'     => 'Training',
                'title'    => $r['title'],
                'date'     => date('D, M j', strtotime($r['dt'])),
                'time'     => date('g:i A', strtotime($r['dt'])),
                'location' => $r['location'] ?: '—',
                'sort'     => $r['dt'],
            ];
        }

        $stmt = $pdo->prepare("
            SELECT m.match_datetime AS dt, m.location,
                   ht.name AS home_name, at.name AS away_name,
                   m.home_team_id, m.away_team_id
            FROM matches m
            JOIN teams ht ON ht.team_id = m.home_team_id
            JOIN teams at ON at.team_id = m.away_team_id
            WHERE m.status = 'scheduled'
              AND (m.home_team_id = :t1 OR m.away_team_id = :t2)
              AND m.match_datetime >= NOW()
            ORDER BY m.match_datetime LIMIT 10
        ");
        $stmt->execute(['t1' => $teamId, 't2' => $teamId]);
        foreach ($stmt->fetchAll() as $r) {
            $opponent = ((int)$r['home_team_id'] === $teamId) ? $r['away_name'] : $r['home_name'];
            $schedule[] = [
                'kind'     => 'Match',
                'title'    => 'vs ' . $opponent,
                'date'     => date('D, M j', strtotime($r['dt'])),
                'time'     => date('g:i A', strtotime($r['dt'])),
                'location' => $r['location'] ?: 'TBD',
                'sort'     => $r['dt'],
            ];
        }
        usort($schedule, fn($a, $b) => $a['sort'] <=> $b['sort']);
        return $schedule;
    }

    /**
     * Determine the app role for a user by checking DB tables.
     * Falls back to metadata if no table association found.
     */
    function sp_detect_role(PDO $pdo, int $userId, ?string $metadataRole): string
    {
        // Check if coach
        $stmt = $pdo->prepare("SELECT 1 FROM team_coaches WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        if ($stmt->fetch()) return 'coach';

        // Check if player
        $stmt = $pdo->prepare("SELECT 1 FROM players WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        if ($stmt->fetch()) return 'player';

        // Check if parent
        $stmt = $pdo->prepare("SELECT 1 FROM parents WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        if ($stmt->fetch()) return 'parent';

        // Fall back to metadata
        if ($metadataRole && in_array($metadataRole, ['coach', 'parent', 'player'], true)) {
            return $metadataRole;
        }

        return 'parent'; // safe default
    }

    function sp_get_user_full_name(PDO $pdo, int $userId): string
    {
        $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return 'Unknown User';
        }
        return trim((string)$row['first_name'] . ' ' . (string)($row['last_name'] ?? ''));
    }

    function sp_get_coach_team_options(PDO $pdo, int $coachUserId): array
    {
        $stmt = $pdo->prepare("
            SELECT t.team_id, t.name
            FROM team_coaches tc
            JOIN teams t ON t.team_id = tc.team_id
            WHERE tc.user_id = :uid
            ORDER BY (tc.role = 'head') DESC, t.team_id DESC
        ");
        $stmt->execute(['uid' => $coachUserId]);
        return $stmt->fetchAll();
    }

    function sp_get_leagues(PDO $pdo): array
    {
        $stmt = $pdo->query("
            SELECT league_id, name, season, sport, status
            FROM leagues
            ORDER BY season DESC, sport ASC, name ASC
        ");
        return $stmt->fetchAll();
    }

    function sp_create_team_for_coach(PDO $pdo, int $coachUserId, int $leagueId, string $name, string $city): array
    {
        if ($leagueId <= 0 || $name === '') {
            return ['ok' => false, 'message' => 'League and team name are required.'];
        }

        $stmt = $pdo->prepare("SELECT season, sport FROM leagues WHERE league_id = :lid LIMIT 1");
        $stmt->execute(['lid' => $leagueId]);
        $league = $stmt->fetch();
        if (!$league) {
            return ['ok' => false, 'message' => 'Selected league does not exist.'];
        }

        $pdo->beginTransaction();
        try {
            $ins = $pdo->prepare("
                INSERT INTO teams (league_id, name, city, season, sport, is_active)
                VALUES (:lid, :name, :city, :season, :sport, 1)
            ");
            $ins->execute([
                'lid' => $leagueId,
                'name' => $name,
                'city' => ($city !== '' ? $city : null),
                'season' => $league['season'],
                'sport' => $league['sport'],
            ]);
            $teamId = (int)$pdo->lastInsertId();

            $assign = $pdo->prepare("
                INSERT INTO team_coaches (team_id, user_id, role)
                VALUES (:tid, :uid, 'head')
            ");
            $assign->execute(['tid' => $teamId, 'uid' => $coachUserId]);

            $pdo->prepare("UPDATE users SET is_coach = 1 WHERE user_id = :uid")->execute(['uid' => $coachUserId]);
            $pdo->commit();

            return ['ok' => true, 'message' => 'Team created successfully.', 'team_id' => $teamId];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'message' => 'Could not create team.'];
        }
    }

    function sp_player_team_directory(PDO $pdo, int $playerUserId): array
    {
        $stmt = $pdo->prepare("SELECT team_id FROM players WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $playerUserId]);
        $currentTeamId = (int)($stmt->fetchColumn() ?: 0);

        $stmt = $pdo->prepare("
            SELECT team_id, status, created_at
            FROM team_join_requests
            WHERE player_user_id = :uid
            ORDER BY request_id DESC
        ");
        $stmt->execute(['uid' => $playerUserId]);
        $allRequests = $stmt->fetchAll();
        $latestByTeam = [];
        foreach ($allRequests as $r) {
            $tid = (int)$r['team_id'];
            if (!isset($latestByTeam[$tid])) {
                $latestByTeam[$tid] = $r;
            }
        }

        $teamsStmt = $pdo->query("
            SELECT t.team_id, t.name AS team_name, t.city, t.season, t.sport,
                   l.name AS league_name
            FROM teams t
            JOIN leagues l ON l.league_id = t.league_id
            WHERE t.is_active = 1
            ORDER BY t.season DESC, t.name ASC
        ");
        $teams = [];
        foreach ($teamsStmt->fetchAll() as $row) {
            $tid = (int)$row['team_id'];
            $coachStmt = $pdo->prepare("
                SELECT u.user_id, u.first_name, u.last_name
                FROM team_coaches tc
                JOIN users u ON u.user_id = tc.user_id
                WHERE tc.team_id = :tid
                ORDER BY (tc.role = 'head') DESC, u.first_name ASC
                LIMIT 1
            ");
            $coachStmt->execute(['tid' => $tid]);
            $coach = $coachStmt->fetch();

            $latest = $latestByTeam[$tid] ?? null;
            $teams[] = [
                'team_id' => $tid,
                'team_name' => $row['team_name'],
                'city' => $row['city'] ?: '-',
                'season' => $row['season'] ?: '-',
                'sport' => $row['sport'] ?: '-',
                'league_name' => $row['league_name'],
                'coach_name' => $coach ? trim($coach['first_name'] . ' ' . $coach['last_name']) : 'Not assigned',
                'request_status' => $latest['status'] ?? null,
                'request_time' => $latest['created_at'] ?? null,
                'is_current' => $currentTeamId > 0 && $currentTeamId === $tid,
            ];
        }

        return [
            'current_team_id' => $currentTeamId,
            'teams' => $teams,
        ];
    }

    function sp_submit_team_join_request(PDO $pdo, int $playerUserId, int $teamId, string $message): array
    {
        if ($teamId <= 0) {
            return ['ok' => false, 'message' => 'Invalid team.'];
        }

        $stmt = $pdo->prepare("SELECT team_id FROM players WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $playerUserId]);
        $currentTeamId = (int)($stmt->fetchColumn() ?: 0);
        if ($currentTeamId === $teamId) {
            return ['ok' => false, 'message' => 'You are already a member of this team.'];
        }

        $stmt = $pdo->prepare("
            SELECT request_id FROM team_join_requests
            WHERE player_user_id = :uid AND team_id = :tid AND status = 'pending'
            LIMIT 1
        ");
        $stmt->execute(['uid' => $playerUserId, 'tid' => $teamId]);
        if ($stmt->fetch()) {
            return ['ok' => false, 'message' => 'A pending request for this team already exists.'];
        }

        $ins = $pdo->prepare("
            INSERT INTO team_join_requests (team_id, player_user_id, message, status)
            VALUES (:tid, :uid, :msg, 'pending')
        ");
        $ins->execute([
            'tid' => $teamId,
            'uid' => $playerUserId,
            'msg' => ($message !== '' ? $message : null),
        ]);

        return ['ok' => true, 'message' => 'Request sent to the coach.'];
    }

    function sp_get_coach_join_requests(PDO $pdo, int $coachUserId): array
    {
        $stmt = $pdo->prepare("
            SELECT r.request_id, r.team_id, r.player_user_id, r.message, r.status, r.created_at,
                   t.name AS team_name,
                   u.first_name, u.last_name, u.email
            FROM team_join_requests r
            JOIN team_coaches tc ON tc.team_id = r.team_id
            JOIN teams t ON t.team_id = r.team_id
            JOIN users u ON u.user_id = r.player_user_id
            WHERE tc.user_id = :uid AND r.status = 'pending'
            ORDER BY r.created_at DESC
        ");
        $stmt->execute(['uid' => $coachUserId]);
        return $stmt->fetchAll();
    }

    function sp_review_join_request(PDO $pdo, int $coachUserId, int $requestId, string $decision): array
    {
        if (!in_array($decision, ['approved', 'rejected'], true)) {
            return ['ok' => false, 'message' => 'Invalid decision.'];
        }

        $stmt = $pdo->prepare("
            SELECT r.request_id, r.team_id, r.player_user_id, r.status
            FROM team_join_requests r
            JOIN team_coaches tc ON tc.team_id = r.team_id
            WHERE r.request_id = :rid AND tc.user_id = :uid
            LIMIT 1
        ");
        $stmt->execute(['rid' => $requestId, 'uid' => $coachUserId]);
        $request = $stmt->fetch();
        if (!$request) {
            return ['ok' => false, 'message' => 'Request not found.'];
        }
        if ($request['status'] !== 'pending') {
            return ['ok' => false, 'message' => 'This request has already been reviewed.'];
        }

        $teamId = (int)$request['team_id'];
        $playerUserId = (int)$request['player_user_id'];

        $pdo->beginTransaction();
        try {
            if ($decision === 'approved') {
                $upsert = $pdo->prepare("
                    INSERT INTO players (user_id, team_id, jersey_number, position)
                    VALUES (:uid, :tid, NULL, NULL)
                    ON DUPLICATE KEY UPDATE team_id = VALUES(team_id)
                ");
                $upsert->execute([
                    'uid' => $playerUserId,
                    'tid' => $teamId,
                ]);

                $notify = $pdo->prepare("
                    INSERT INTO direct_messages (team_id, sender_user_id, recipient_user_id, body)
                    VALUES (:tid, :sid, :rid, :body)
                ");
                $notify->execute([
                    'tid' => $teamId,
                    'sid' => $coachUserId,
                    'rid' => $playerUserId,
                    'body' => 'Your join request was approved. Welcome to the team.',
                ]);
            }

            $update = $pdo->prepare("
                UPDATE team_join_requests
                SET status = :status, reviewed_by = :reviewer, reviewed_at = NOW()
                WHERE request_id = :rid
            ");
            $update->execute([
                'status' => $decision,
                'reviewer' => $coachUserId,
                'rid' => $requestId,
            ]);

            $pdo->commit();
            return ['ok' => true, 'message' => $decision === 'approved' ? 'Request approved.' : 'Request rejected.'];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'message' => 'Could not process request.'];
        }
    }

    function sp_get_coach_player_threads(PDO $pdo, int $coachUserId): array
    {
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.profile_image, p.team_id, p.jersey_number, p.position, t.name AS team_name
            FROM team_coaches tc
            JOIN players p ON p.team_id = tc.team_id
            JOIN users u ON u.user_id = p.user_id
            JOIN teams t ON t.team_id = p.team_id
            WHERE tc.user_id = :uid
            ORDER BY t.name ASC, u.first_name ASC, u.last_name ASC
        ");
        $stmt->execute(['uid' => $coachUserId]);
        return $stmt->fetchAll();
    }

    function sp_get_player_coach_threads(PDO $pdo, int $playerUserId): array
    {
        $stmt = $pdo->prepare("
            SELECT p.team_id, t.name AS team_name
            FROM players p
            JOIN teams t ON t.team_id = p.team_id
            WHERE p.user_id = :uid
            LIMIT 1
        ");
        $stmt->execute(['uid' => $playerUserId]);
        $playerTeam = $stmt->fetch();
        if (!$playerTeam) {
            return [];
        }

        $coachStmt = $pdo->prepare("
            SELECT u.user_id, u.first_name, u.last_name, u.profile_image, tc.role, t.name AS team_name
            FROM team_coaches tc
            JOIN users u ON u.user_id = tc.user_id
            JOIN teams t ON t.team_id = tc.team_id
            WHERE tc.team_id = :tid
            ORDER BY (tc.role = 'head') DESC, u.first_name ASC
        ");
        $coachStmt->execute(['tid' => (int)$playerTeam['team_id']]);
        return $coachStmt->fetchAll();
    }

    function sp_get_direct_messages(PDO $pdo, int $userId, int $otherUserId, int $limit = 60): array
    {
        $stmt = $pdo->prepare("
            SELECT message_id, sender_user_id, recipient_user_id, body, is_read, created_at
            FROM direct_messages
            WHERE (sender_user_id = :a1 AND recipient_user_id = :b1)
               OR (sender_user_id = :b2 AND recipient_user_id = :a2)
            ORDER BY message_id DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':a1', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':b1', $otherUserId, PDO::PARAM_INT);
        $stmt->bindValue(':b2', $otherUserId, PDO::PARAM_INT);
        $stmt->bindValue(':a2', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_reverse($stmt->fetchAll());
    }

    function sp_get_direct_messages_since(PDO $pdo, int $userId, int $otherUserId, int $sinceMessageId, int $limit = 80): array
    {
        $stmt = $pdo->prepare("
            SELECT message_id, sender_user_id, recipient_user_id, body, is_read, created_at
            FROM direct_messages
            WHERE (
                    (sender_user_id = :a1 AND recipient_user_id = :b1)
                 OR (sender_user_id = :b2 AND recipient_user_id = :a2)
                  )
              AND message_id > :since_id
            ORDER BY message_id ASC
            LIMIT :lim
        ");
        $stmt->bindValue(':a1', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':b1', $otherUserId, PDO::PARAM_INT);
        $stmt->bindValue(':b2', $otherUserId, PDO::PARAM_INT);
        $stmt->bindValue(':a2', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':since_id', $sinceMessageId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    function sp_send_direct_message(PDO $pdo, int $senderUserId, int $recipientUserId, ?int $teamId, string $body): bool
    {
        $body = trim($body);
        if ($body === '') {
            return false;
        }

        $stmt = $pdo->prepare("
            INSERT INTO direct_messages (team_id, sender_user_id, recipient_user_id, body)
            VALUES (:tid, :sid, :rid, :body)
        ");
        return $stmt->execute([
            'tid' => $teamId > 0 ? $teamId : null,
            'sid' => $senderUserId,
            'rid' => $recipientUserId,
            'body' => $body,
        ]);
    }

    function sp_mark_thread_as_read(PDO $pdo, int $currentUserId, int $otherUserId): void
    {
        $stmt = $pdo->prepare("
            UPDATE direct_messages
            SET is_read = 1, read_at = NOW()
            WHERE recipient_user_id = :uid
              AND sender_user_id = :other
              AND is_read = 0
        ");
        $stmt->execute([
            'uid' => $currentUserId,
            'other' => $otherUserId,
        ]);
    }

    function sp_unread_counts_for_user(PDO $pdo, int $userId): array
    {
        $stmt = $pdo->prepare("
            SELECT sender_user_id, COUNT(*) AS unread_count
            FROM direct_messages
            WHERE recipient_user_id = :uid AND is_read = 0
            GROUP BY sender_user_id
        ");
        $stmt->execute(['uid' => $userId]);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int)$row['sender_user_id']] = (int)$row['unread_count'];
        }
        return $map;
    }

    function sp_can_user_chat_with(PDO $pdo, int $currentUserId, int $otherUserId, string $role): bool
    {
        if ($otherUserId <= 0) {
            return false;
        }

        if ($role === 'coach') {
            foreach (sp_get_coach_player_threads($pdo, $currentUserId) as $t) {
                if ((int)$t['user_id'] === $otherUserId) {
                    return true;
                }
            }
            return false;
        }

        if ($role === 'player') {
            foreach (sp_get_player_coach_threads($pdo, $currentUserId) as $t) {
                if ((int)$t['user_id'] === $otherUserId) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }
}
