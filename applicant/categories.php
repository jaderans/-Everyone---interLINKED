<?php
require_once 'db_config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'Next') {
        $category = $_POST['category'] ?? '';
        $skills = $_POST['skills'] ?? [];

        if (!empty($category) && !empty($skills)) {
            $_SESSION['application_data']['user_categ'] = $category;
            $_SESSION['application_data']['user_skills'] = implode(',', $skills);
            $_SESSION['application_data']['current_step'] = 2;

            header('Location: title.php');
            exit;
        } else {
            $error = "Please select a category and at least one skill.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application | Category</title>
    <link rel="icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="authenticate.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
<div class="topvar">
    <div class="logo">
        <img src="../imgs/inl2Logo.png" alt="INTERLINKED Logo">
    </div>
</div>
<div class="container">
    <p class="subtext">1/8</p>
    <h1>What kind of work you are applying for?</h1>
    <p class="subtext">Don't worry you can edit it later.</p>

    <?php if (isset($error)): ?>
        <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" id="categoryForm">
        <div class="category-specialty-container">
            <div class="category-col">
                <div class="category-header">Select 1 category</div>
                <ul class="category-list" id="categoryList">
                    <li data-category="3D Modelling" class="<?php echo ($_SESSION['application_data']['user_categ'] === '3D Modelling') ? 'selected' : ''; ?>">3D Modelling</li>
                    <li data-category="Illustration" class="<?php echo ($_SESSION['application_data']['user_categ'] === 'Illustration') ? 'selected' : ''; ?>">Illustration</li>
                    <li data-category="Photo Manipulation" class="<?php echo ($_SESSION['application_data']['user_categ'] === 'Photo Manipulation') ? 'selected' : ''; ?>">Photo Manipulation</li>
                    <li data-category="Layout Design" class="<?php echo ($_SESSION['application_data']['user_categ'] === 'Layout Design') ? 'selected' : ''; ?>">Layout Design</li>
                    <li data-category="UI/UX Design" class="<?php echo ($_SESSION['application_data']['user_categ'] === 'UI/UX Design') ? 'selected' : ''; ?>">UI/UX Design</li>
                </ul>
                <input type="hidden" name="category" id="selectedCategory" value="<?php echo htmlspecialchars($_SESSION['application_data']['user_categ']); ?>">
            </div>
            <div class="specialty-col">
                <div class="specialty-header">Now, select your Skills</div>
                <ul class="specialty-list" id="specialtyList">
                </ul>
                <a href="#" class="clear-selections" id="clearSelections">✕ Clear selections</a>
            </div>
        </div>
    </form>
</div>
<div class="botvar">
    <div class="botconright">
        <input type="submit" form="categoryForm" name="action" value="Next">
    </div>
</div>

<script>
    const specialtiesData = {
        "3D Modelling": [
            "Product Visualization",
            "Character Design",
            "Game Asset Creation",
            "Architectural Rendering",
            "Animation Basics",
            "Texturing & Shading"
        ],
        "Illustration": [
            "Character Illustration",
            "Children's Book Illustration",
            "Concept Art",
            "Digital Painting",
            "Vector Art",
            "Storyboarding"
        ],
        "Photo Manipulation": [
            "Image Retouching",
            "Surreal Compositing",
            "Background Removal",
            "Color Correction",
            "Lighting Effects",
            "Creative Collaging"
        ],
        "Layout Design": [
            "Magazine Layouts",
            "Brochure Design",
            "Typography",
            "Grid Systems",
            "Editorial Design",
            "Print Production Prep"
        ],
        "UI/UX Design": [
            "Wireframing",
            "Prototyping",
            "User Research",
            "Usability Testing",
            "Interaction Design",
            "Design Systems"
        ]
    };

    const categoryList = document.getElementById('categoryList');
    const specialtyList = document.getElementById('specialtyList');
    const clearSelections = document.getElementById('clearSelections');
    const selectedCategoryInput = document.getElementById('selectedCategory');

    // Restore selected skills from session
    const selectedSkills = '<?php echo $_SESSION['application_data']['user_skills']; ?>'.split(',').filter(s => s.length > 0);

    function renderSpecialties(category) {
        specialtyList.innerHTML = '';
        (specialtiesData[category] || []).forEach(name => {
            const li = document.createElement('li');
            const isChecked = selectedSkills.includes(name) ? 'checked' : '';
            li.innerHTML = `<label><input type="checkbox" name="skills[]" value="${name}" ${isChecked}> ${name}</label>`;
            specialtyList.appendChild(li);
        });
    }

    categoryList.addEventListener('click', function(e) {
        if (e.target.tagName === 'LI') {
            // Update selected state
            Array.from(categoryList.children).forEach(li => li.classList.remove('selected'));
            e.target.classList.add('selected');

            // Update hidden input
            selectedCategoryInput.value = e.target.getAttribute('data-category');

            // Render specialties
            renderSpecialties(e.target.textContent);
        }
    });

    // Initial render if category is selected
    if (selectedCategoryInput.value) {
        renderSpecialties(selectedCategoryInput.value);
    }

    // Clear selections
    clearSelections.addEventListener('click', function(e) {
        e.preventDefault();
        specialtyList.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    });
</script>
</body>
</html>