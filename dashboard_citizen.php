<?php
require_once 'includes/db.php';
requireRole('citizen');
$user = $_SESSION['user'];
$citizenId = $user['id'];
$citizen = [];
$mp = null;
$announcements = [];
$projects = [];
$recentComplaints = [];
$recentSuggestions = [];

$stmt = $mysqli->prepare('SELECT citizens.name, citizens.email, citizens.phone, sectors.name AS sector_name, constituencies.name AS constituency_name, mps.id AS mp_id, mps.name AS mp_name, mps.email AS mp_email FROM citizens LEFT JOIN sectors ON citizens.sector_id = sectors.id LEFT JOIN constituencies ON sectors.constituency_id = constituencies.id LEFT JOIN mps ON sectors.mp_id = mps.id WHERE citizens.id = ?');
$stmt->bind_param('i', $citizenId);
$stmt->execute();
$citizen = $stmt->get_result()->fetch_assoc();

if ($citizen && $citizen['mp_id']) {
    $mp = [
        'id' => $citizen['mp_id'],
        'name' => $citizen['mp_name'],
        'email' => $citizen['mp_email'],
    ];
    $announceStmt = $mysqli->prepare('SELECT title, message, created_at FROM announcements WHERE mp_id = ? ORDER BY created_at DESC LIMIT 3');
    $announceStmt->bind_param('i', $mp['id']);
    $announceStmt->execute();
    $announcements = $announceStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $projectStmt = $mysqli->prepare('SELECT title, description, status, created_at FROM mp_projects WHERE mp_id = ? ORDER BY created_at DESC LIMIT 3');
    $projectStmt->bind_param('i', $mp['id']);
    $projectStmt->execute();
    $projects = $projectStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$complaintCount = (int) $mysqli->query("SELECT COUNT(*) AS total FROM complaints WHERE citizen_id = $citizenId")->fetch_assoc()['total'];
$suggestionCount = (int) $mysqli->query("SELECT COUNT(*) AS total FROM suggestions WHERE citizen_id = $citizenId")->fetch_assoc()['total'];
$crimeCount = (int) $mysqli->query("SELECT COUNT(*) AS total FROM crime_reports WHERE citizen_id = $citizenId")->fetch_assoc()['total'];

$complaintStmt = $mysqli->prepare('SELECT complaints.subject, complaint_status.name AS status_name, complaints.created_at FROM complaints LEFT JOIN complaint_status ON complaints.status_id = complaint_status.id WHERE complaints.citizen_id = ? ORDER BY complaints.created_at DESC LIMIT 4');
$complaintStmt->bind_param('i', $citizenId);
$complaintStmt->execute();
$recentComplaints = $complaintStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$suggestionStmt = $mysqli->prepare('SELECT title, created_at FROM suggestions WHERE citizen_id = ? ORDER BY created_at DESC LIMIT 4');
$suggestionStmt->bind_param('i', $citizenId);
$suggestionStmt->execute();
$recentSuggestions = $suggestionStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>

<div class="mb-4" style="animation-delay: 0.1s;">
    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; gap: 30px; flex-wrap: wrap;">
        <div>
            <h1
                style="font-size: 2.8rem; font-weight: 800; margin-bottom: 8px; letter-spacing: -1.5px; line-height: 1.1;">
                Dashboard</h1>
            <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">
                Welcome back, <span
                    style="color: var(--primary-dark); font-weight: 700;"><?= htmlspecialchars($citizen['name']) ?></span>
                •
                <?= htmlspecialchars($citizen['sector_name']) ?>, <?= htmlspecialchars($citizen['constituency_name']) ?>
            </p>
        </div>
        <div style="display: flex; gap: 16px; flex-shrink: 0;">
            <a href="submit_complaint.php" class="button" style="width: auto;">Submit Complaint</a>
            <a href="submit_crime.php" class="button"
                style="background: var(--accent); box-shadow: 0 4px 15px rgba(244, 63, 94, 0.3); width: auto;">Report
                Crime</a>
        </div>
    </div>
</div>

<div class="grid mb-4" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
    <!-- Stats Cards -->
    <div class="card" style="display: flex; align-items: center; gap: 20px; animation-delay: 0.2s;">
        <div style="background: rgba(99, 102, 241, 0.1); padding: 15px; border-radius: 12px;">
            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="var(--primary)" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
        </div>
        <div>
            <p class="text-muted"
                style="font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Complaints
            </p>
            <h3 style="font-size: 1.8rem; margin: 0;"><?= $complaintCount ?></h3>
        </div>
    </div>

    <div class="card" style="display: flex; align-items: center; gap: 20px; animation-delay: 0.3s;">
        <div style="background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 12px;">
            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="var(--secondary)" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
        </div>
        <div>
            <p class="text-muted"
                style="font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Suggestions
            </p>
            <h3 style="font-size: 1.8rem; margin: 0;"><?= $suggestionCount ?></h3>
        </div>
    </div>

    <div class="card" style="display: flex; align-items: center; gap: 20px; animation-delay: 0.4s;">
        <div style="background: rgba(244, 63, 94, 0.1); padding: 15px; border-radius: 12px;">
            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="var(--accent)" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z">
                </path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <div>
            <p class="text-muted"
                style="font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Crimes
                Reported</p>
            <h3 style="font-size: 1.8rem; margin: 0;"><?= $crimeCount ?></h3>
        </div>
    </div>
</div>

<div class="grid mb-4" style="grid-template-columns: 2fr 1fr;">
    <!-- Main Activity Section -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <div class="card" style="animation-delay: 0.5s;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2 style="margin: 0;"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor"
                        stroke-width="2" style="margin-right: 12px;">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>Recent Complaints</h2>
                <a href="manage_my_submissions.php" class="read-more">View All &rarr;</a>
            </div>
            <?php if ($recentComplaints): ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentComplaints as $item): ?>
                                <tr>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($item['subject']) ?></td>
                                    <td><span
                                            class="badge <?= strtolower($item['status_name']) === 'resolved' ? 'success' : 'primary' ?>"><?= htmlspecialchars($item['status_name'] ?: 'New') ?></span>
                                    </td>
                                    <td class="text-muted"><?= date('M d, Y', strtotime($item['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <h3>No complaints found</h3>
                    <p>You haven't filed any complaints yet. Your voice matters in shaping our constituency.</p>
                    <a href="submit_complaint.php" class="button outline">File your first complaint</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="card" style="animation-delay: 0.6s;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2 style="margin: 0;"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor"
                        stroke-width="2" style="margin-right: 12px;">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>Constituency Announcements</h2>
                <a href="view_announcements.php" class="read-more">See All &rarr;</a>
            </div>
            <?php if ($announcements): ?>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <?php foreach ($announcements as $item): ?>
                        <div style="padding: 20px; border-radius: 12px; background: #f8fafc; border: 1px solid var(--border);">
                            <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; color: var(--primary-dark);">
                                <?= htmlspecialchars($item['title']) ?></h4>
                            <p style="font-size: 0.95rem; margin-bottom: 12px;"><?= nl2br(htmlspecialchars($item['message'])) ?>
                            </p>
                            <small class="text-muted"
                                style="font-weight: 600;"><?= date('M d, Y h:i A', strtotime($item['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <h3>No announcements</h3>
                    <p>There are no recent announcements from your MP at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Section -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <div class="card"
            style="animation-delay: 0.7s; background: var(--primary-gradient); color: white; padding: 30px;">
            <h2 style="color: white; margin-bottom: 20px; font-size: 1.4rem;">Your Representative</h2>
            <?php if ($mp): ?>
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                    <div
                        style="width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800;">
                        <?= substr($mp['name'], 0, 1) ?>
                    </div>
                    <div>
                        <h4 style="font-size: 1.2rem; font-weight: 800; margin: 0;"><?= htmlspecialchars($mp['name']) ?>
                        </h4>
                        <p style="font-size: 0.85rem; opacity: 0.8;">Member of Parliament</p>
                    </div>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 12px; margin-bottom: 20px;">
                    <p style="font-size: 0.9rem; margin-bottom: 5px; opacity: 0.8;">Email Contact</p>
                    <p style="font-weight: 600;"><?= htmlspecialchars($mp['email']) ?></p>
                </div>
                <a href="mailto:<?= htmlspecialchars($mp['email']) ?>" class="button"
                    style="background: white; color: var(--primary-dark); width: 100%; box-shadow: none;">Send Direct
                    Message</a>
            <?php else: ?>
                <div class="empty-state" style="background: rgba(255,255,255,0.1); border: none;">
                    <h3 style="color: white;">MP Not Assigned</h3>
                    <p style="color: rgba(255,255,255,0.7);">Your sector's representative hasn't been linked yet.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="card" style="animation-delay: 0.8s;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 1.3rem;">Ongoing Projects</h2>
                <a href="view_projects.php" class="read-more">All &rarr;</a>
            </div>
            <?php if ($projects): ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach ($projects as $project): ?>
                        <div style="border-left: 3px solid var(--primary); padding-left: 15px; margin-bottom: 5px;">
                            <h5 style="font-size: 1rem; margin-bottom: 4px;"><?= htmlspecialchars($project['title']) ?></h5>
                            <span class="badge <?= strtolower($project['status']) === 'completed' ? 'success' : 'primary' ?>"
                                style="font-size: 0.65rem;"><?= htmlspecialchars($project['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted" style="font-size: 0.9rem;">No active projects in your sector.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>