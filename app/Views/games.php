<?php
/*
================================================================================
BESTAND: /app/Views/games.php (FULLY TRANSLATED)
================================================================================
*/
?>
    <section class="games">
        <div class="container">
            <h1><i class="fas fa-gamepad"></i> <span data-translate="games_title"><?= trans('games_title') ?></span></h1>
            <div class="games-tabs">
                <button class="tab-btn active" data-tab="minecraft">
                    <i class="fas fa-cube"></i> <span data-translate="games_minecraft"><?= trans('games_minecraft') ?></span>
                </button>
                <button class="tab-btn" data-tab="r6siege">
                    <i class="fas fa-crosshairs"></i> <span data-translate="games_r6siege"><?= trans('games_r6siege') ?></span>
                </button>
            </div>
            <div class="tab-content active" id="minecraft">
                <div class="stats-card">
                    <div class="stats-header">
                        <h2><?= htmlspecialchars($minecraft['server_name']) ?></h2>
                        <span class="status-badge <?= $minecraft['online'] ? 'online' : 'offline' ?>">
                        <i class="fas fa-circle"></i>
                        <span data-translate="<?= $minecraft['online'] ? 'status_online' : 'status_offline' ?>">
                            <?= trans($minecraft['online'] ? 'status_online' : 'status_offline') ?>
                        </span>
                    </span>
                    </div>
                    <div class="server-info">
                        <p><strong><span data-translate="server_ip">IP</span>:</strong> <?= htmlspecialchars($minecraft['server_ip']) ?></p>
                        <p><strong><span data-translate="server_version">Versie</span>:</strong> <?= htmlspecialchars($minecraft['version']) ?></p>
                        <p><strong>MOTD:</strong> <?= htmlspecialchars($minecraft['motd']) ?></p>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <span class="stat-number"><?= $minecraft['online_players'] ?>/<?= $minecraft['max_players'] ?></span>
                                <span class="stat-label" data-translate="players_online"><?= trans('players_online') ?></span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <span class="stat-number"><?= htmlspecialchars($minecraft['uptime']) ?></span>
                                <span class="stat-label">Uptime</span>
                            </div>
                        </div>
                    </div>
                    <h3><i class="fas fa-trophy"></i> <span data-translate="top_players"><?= trans('top_players') ?></span></h3>
                    <div class="top-players">
                        <?php foreach ($minecraft['top_players'] as $index => $player): ?>
                            <div class="player-item">
                                <span class="rank">#<?= $index + 1 ?></span>
                                <span class="player-name"><?= htmlspecialchars($player['name']) ?></span>
                                <span class="player-rank"><?= htmlspecialchars($player['rank']) ?></span>
                                <span class="playtime"><?= htmlspecialchars($player['playtime']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="last-update">
                        <i class="fas fa-sync-alt"></i>
                        <span data-translate="games_last_update"><?= trans('games_last_update') ?></span>:
                        <?= date('d-m-Y H:i', $minecraft['last_update']) ?>
                    </p>
                </div>
            </div>
            <div class="tab-content" id="r6siege">
                <div class="stats-card">
                    <div class="stats-header">
                        <h2>Rainbow Six Siege</h2>
                        <span class="platform-badge">
                        <i class="fas fa-desktop"></i> <?= htmlspecialchars($r6siege['platform']) ?>
                    </span>
                    </div>
                    <div class="player-info">
                        <p><strong><span data-translate="username">Username</span>:</strong> <?= htmlspecialchars($r6siege['username']) ?></p>
                        <p><strong><span data-translate="level">Level</span>:</strong> <?= htmlspecialchars($r6siege['level']) ?></p>
                        <p><strong><span data-translate="playtime">Playtime</span>:</strong> <?= htmlspecialchars($r6siege['playtime']) ?></p>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <i class="fas fa-trophy"></i>
                            <div>
                                <span class="stat-number"><?= htmlspecialchars($r6siege['current_rank']) ?></span>
                                <span class="stat-label" data-translate="current_rank"><?= trans('current_rank') ?></span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-star"></i>
                            <div>
                                <span class="stat-number"><?= htmlspecialchars($r6siege['max_rank']) ?></span>
                                <span class="stat-label" data-translate="highest_rank"><?= trans('highest_rank') ?></span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-crosshairs"></i>
                            <div>
                                <span class="stat-number"><?= htmlspecialchars($r6siege['kd_ratio']) ?></span>
                                <span class="stat-label">K/D Ratio</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-percentage"></i>
                            <div>
                                <span class="stat-number"><?= htmlspecialchars($r6siege['win_rate']) ?></span>
                                <span class="stat-label" data-translate="win_rate"><?= trans('win_rate') ?></span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-chart-line"></i>
                            <div>
                                <span class="stat-number"><?= htmlspecialchars($r6siege['mmr']) ?></span>
                                <span class="stat-label">MMR</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-user-shield"></i>
                            <div>
                                <span class="stat-number"><?= htmlspecialchars($r6siege['favorite_operator']) ?></span>
                                <span class="stat-label" data-translate="favorite_operator"><?= trans('favorite_operator') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="detailed-stats">
                        <h3><i class="fas fa-chart-bar"></i> <span data-translate="detailed_statistics"><?= trans('detailed_statistics') ?></span></h3>
                        <div class="stats-row">
                            <div class="stat-detail">
                                <span class="stat-value"><?= htmlspecialchars($r6siege['kills']) ?></span>
                                <span class="stat-name" data-translate="kills"><?= trans('kills') ?></span>
                            </div>
                            <div class="stat-detail">
                                <span class="stat-value"><?= htmlspecialchars($r6siege['deaths']) ?></span>
                                <span class="stat-name" data-translate="deaths"><?= trans('deaths') ?></span>
                            </div>
                            <div class="stat-detail">
                                <span class="stat-value"><?= htmlspecialchars($r6siege['wins']) ?></span>
                                <span class="stat-name" data-translate="wins"><?= trans('wins') ?></span>
                            </div>
                            <div class="stat-detail">
                                <span class="stat-value"><?= htmlspecialchars($r6siege['losses']) ?></span>
                                <span class="stat-name" data-translate="losses"><?= trans('losses') ?></span>
                            </div>
                        </div>
                    </div>
                    <p class="last-update">
                        <i class="fas fa-sync-alt"></i>
                        <span data-translate="games_last_update"><?= trans('games_last_update') ?></span>:
                        <?= date('d-m-Y H:i', $r6siege['last_update']) ?>
                    </p>
                </div>
            </div>
        </div>
    </section>
<?php