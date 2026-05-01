<?php
require_once 'includes/db.php';

$sectors = [];
$result = $mysqli->query('SELECT sectors.id, sectors.name AS sector_name, constituencies.name AS constituency_name, mps.name AS mp_name FROM sectors LEFT JOIN constituencies ON sectors.constituency_id = constituencies.id LEFT JOIN mps ON sectors.mp_id = mps.id ORDER BY sectors.name');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sectors[] = $row;
    }
}

$updates = [];
$updatesResult = $mysqli->query('
    SELECT title, message AS description, created_at, "Announcement" AS type FROM announcements 
    UNION ALL 
    SELECT title, description, created_at, "Project" AS type FROM mp_projects 
    ORDER BY created_at DESC LIMIT 4
');
if ($updatesResult) {
    while ($row = $updatesResult->fetch_assoc()) {
        $updates[] = $row;
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="hero-slider">
    <!-- Padma Bridge -->
    <div class="slide active" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/The_padma_bridge_02.jpg/1280px-The_padma_bridge_02.jpg');">
        <div class="slide-overlay">
            <div class="slide-content">
                <span class="badge primary mb-4" style="font-size: 0.9rem; padding: 8px 16px;">Welcome to Constituency Hub</span>
                <h1>Digital Constituency <br> Management</h1>
                <p>Connecting citizens with MPs to track and shape major infrastructure developments across Bangladesh with transparency and efficiency.</p>
                <div style="display: flex; gap: 20px;">
                    <a class="button" href="register.php" style="padding: 18px 40px; font-size: 1.1rem;">Register as Citizen</a>
                    <a class="button outline" href="login.php" style="padding: 18px 40px; font-size: 1.1rem; border-color: white; color: white;">Citizen Login</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Metro Rail -->
    <div class="slide" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/6/66/Uttara_Uttor_Dhaka_Metro_Rail_Station_platform_3.jpg/1280px-Uttara_Uttor_Dhaka_Metro_Rail_Station_platform_3.jpg');">
        <div class="slide-overlay">
            <div class="slide-content">
                <span class="badge success mb-4" style="font-size: 0.9rem; padding: 8px 16px;">Community & Transit</span>
                <h1>Track Railway <br> Developments</h1>
                <p>Stay updated on the latest transit projects in your constituency. Submit feedback directly to your local representative and see the progress.</p>
                <div style="display: flex; gap: 20px;">
                    <a class="button" href="register.php" style="padding: 18px 40px; font-size: 1.1rem;">Join Your Sector</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flyover -->
    <div class="slide" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/7/7d/Drone_view_of_Kuril_Flyover_Highway_areas_in_Dhaka_City.jpg/1280px-Drone_view_of_Kuril_Flyover_Highway_areas_in_Dhaka_City.jpg');">
        <div class="slide-overlay">
            <div class="slide-content">
                <span class="badge mb-4" style="background:#fef08a; color:#854d0e; font-size: 0.9rem; padding: 8px 16px;">Urban Infrastructure</span>
                <h1>Report & Fix <br> Urban Issues</h1>
                <p>MPs can seamlessly manage constituency issues, prioritize flyover fixes, and directly address the concerns of citizens in real-time.</p>
                <div style="display: flex; gap: 20px;">
                    <a class="button" href="register.php?role=mp" style="padding: 18px 40px; font-size: 1.1rem;">Register as MP</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="slider-nav">
        <div class="slider-dot active" onclick="goToSlide(0)"></div>
        <div class="slider-dot" onclick="goToSlide(1)"></div>
        <div class="slider-dot" onclick="goToSlide(2)"></div>
    </div>
</section>

<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slider-dot');
    
    function goToSlide(n) {
        slides[currentSlide].classList.remove('active');
        dots[currentSlide].classList.remove('active');
        currentSlide = (n + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }
    
    setInterval(() => {
        if(slides.length > 0) goToSlide(currentSlide + 1);
    }, 6000);
</script>

<div class="mb-4">
    <h2 class="section-title">
        Ongoing Infrastructure Focus
    </h2>
    <div class="grid">
        <div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-lg);">
            <div style="height: 200px; background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/The_padma_bridge_02.jpg/1280px-The_padma_bridge_02.jpg'); background-size: cover; background-position: center;"></div>
            <div style="padding: 30px;">
                <h3 style="font-size: 1.4rem; margin-bottom: 12px;">Road & Highway Construction</h3>
                <p class="text-muted" style="margin-bottom: 20px; font-size: 0.95rem;">Tracking the development of major highways, local roads, and bridges ensuring seamless connectivity across divisions.</p>
                <a href="register.php" class="read-more" style="font-weight: 700;">Report Road Issue &rarr;</a>
            </div>
        </div>
        
        <div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-lg);">
            <div style="height: 200px; background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/6/66/Uttara_Uttor_Dhaka_Metro_Rail_Station_platform_3.jpg/1280px-Uttara_Uttor_Dhaka_Metro_Rail_Station_platform_3.jpg'); background-size: cover; background-position: center;"></div>
            <div style="padding: 30px;">
                <h3 style="font-size: 1.4rem; margin-bottom: 12px;">Railway & Metro Expansions</h3>
                <p class="text-muted" style="margin-bottom: 20px; font-size: 0.95rem;">Updates on the ongoing Metro Rail phases, inter-city railway upgrades, and station modernizations directly impacting commuting.</p>
                <a href="register.php" class="read-more" style="font-weight: 700;">Track Transit Projects &rarr;</a>
            </div>
        </div>

        <div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-lg);">
            <div style="height: 200px; background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/7/7d/Drone_view_of_Kuril_Flyover_Highway_areas_in_Dhaka_City.jpg/1280px-Drone_view_of_Kuril_Flyover_Highway_areas_in_Dhaka_City.jpg'); background-size: cover; background-position: center;"></div>
            <div style="padding: 30px;">
                <h3 style="font-size: 1.4rem; margin-bottom: 12px;">Flyover Fixes & Maintenance</h3>
                <p class="text-muted" style="margin-bottom: 20px; font-size: 0.95rem;">Monitoring the structural integrity, daily maintenance, and lighting fixes for major urban flyovers and elevate expressways.</p>
                <a href="register.php" class="read-more" style="font-weight: 700;">View Maintenance Logs &rarr;</a>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4 mb-4" style="padding: 50px; border-radius: var(--radius-lg);">
    <h2 style="font-size: 2rem; margin-bottom: 15px;">Available Constituencies & Sectors</h2>
    <p class="text-muted mb-4" style="font-size: 1.1rem;">Citizens must register under their respective sector to interact with their elected Member of Parliament.</p>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Sector Name</th>
                    <th>Constituency</th>
                    <th>Representative (MP)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sectors)): ?>
                    <tr><td colspan="3" class="text-center" style="padding: 40px;">No sectors currently registered in the database.</td></tr>
                <?php else: ?>
                    <?php foreach ($sectors as $sector): ?>
                        <tr>
                            <td><strong style="color: var(--primary-dark);"><?= htmlspecialchars($sector['sector_name']) ?></strong></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($sector['constituency_name']) ?></td>
                            <td>
                                <?php if ($sector['mp_name']): ?>
                                    <span class="badge success"><?= htmlspecialchars($sector['mp_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge" style="background:#f1f5f9; color:#94a3b8;">Unassigned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>