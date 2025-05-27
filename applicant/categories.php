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
    <link rel="stylesheet" href="authenticate.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .subtext {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #f5c6cb;
            margin-bottom: 1rem;
        }

        .category-specialty-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .category-col, .specialty-col {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .category-header, .specialty-header {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .category-list, .specialty-list {
            list-style: none;
        }

        .category-list li {
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border: 2px solid #e9ecef;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #f8f9fa;
        }

        .category-list li:hover {
            border-color: #1f4c4b;
            background: #e7f3ff;
        }

        .category-list li.selected {
            border-color: #4a8c8a;
            background: #1f4c4b;
            color: white;
        }

        .specialty-list li {
            margin-bottom: 0.5rem;
        }

        .specialty-list label {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 0.25rem;
            transition: background-color 0.2s ease;
        }

        .specialty-list label:hover {
            background-color: #f8f9fa;
        }

        .specialty-list input[type="checkbox"] {
            margin-right: 0.5rem;
            width: 1rem;
            height: 1rem;
        }

        .clear-selections {
            color: #dc3545;
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: 1rem;
            display: inline-block;
        }

        .clear-selections:hover {
            text-decoration: underline;
        }

        .botvar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 1rem 2rem;
            border-top: 1px solid #e9ecef;
        }


        input[type="submit"] {
            background: #1f4c4b;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 0.375rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        input[type="submit"]:hover {
            background: #0056b3;
        }

        input[type="submit"]:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .category-specialty-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .container {
                margin-bottom: 6rem;
            }
        }
    </style>
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

    <div id="errorMessage" class="error-message" style="display: none;"></div>

    <form method="POST" id="categoryForm">
        <div class="category-specialty-container">
            <div class="category-col">
                <div class="category-header">Select 1 category</div>
                <ul class="category-list" id="categoryList">
                    <li data-category="3D Modelling">3D Modelling</li>
                    <li data-category="Illustration">Illustration</li>
                    <li data-category="Photo Manipulation">Photo Manipulation</li>
                    <li data-category="Layout Design">Layout Design</li>
                    <li data-category="UI/UX Design">UI/UX Design</li>
                </ul>
                <input type="hidden" name="category" id="selectedCategory" value="">
            </div>
            <div class="specialty-col">
                <div class="specialty-header">Now, select your Skills</div>
                <ul class="specialty-list" id="specialtyList">
                    <li style="color: #6c757d; font-style: italic;">Please select a category first</li>
                </ul>
                <a href="#" class="clear-selections" id="clearSelections" style="display: none;">✕ Clear selections</a>
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
    const nextButton = document.getElementById('nextButton');
    const errorMessage = document.getElementById('errorMessage');

    function renderSpecialties(category) {
        specialtyList.innerHTML = '';
        const specialties = specialtiesData[category] || [];

        specialties.forEach(name => {
            const li = document.createElement('li');
            li.innerHTML = `<label><input type="checkbox" name="skills[]" value="${name}"> ${name}</label>`;
            specialtyList.appendChild(li);
        });

        // Show clear selections link if there are specialties
        clearSelections.style.display = specialties.length > 0 ? 'inline-block' : 'none';
    }

    function updateNextButton() {
        const hasCategory = selectedCategoryInput.value !== '';
        const checkedSkills = specialtyList.querySelectorAll('input[type="checkbox"]:checked');
        const hasSkills = checkedSkills.length > 0;

        nextButton.disabled = !(hasCategory && hasSkills);
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        setTimeout(() => {
            errorMessage.style.display = 'none';
        }, 5000);
    }

    // Category selection
    categoryList.addEventListener('click', function(e) {
        if (e.target.tagName === 'LI') {
            // Update selected state
            Array.from(categoryList.children).forEach(li => li.classList.remove('selected'));
            e.target.classList.add('selected');

            // Update hidden input
            const category = e.target.getAttribute('data-category');
            selectedCategoryInput.value = category;

            // Render specialties
            renderSpecialties(category);

            // Update button state
            updateNextButton();
        }
    });

    // Skills selection (event delegation)
    specialtyList.addEventListener('change', function(e) {
        if (e.target.type === 'checkbox') {
            updateNextButton();
        }
    });

    // Clear selections
    clearSelections.addEventListener('click', function(e) {
        e.preventDefault();
        specialtyList.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        updateNextButton();
    });

    // Form submission validation
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        const category = selectedCategoryInput.value;
        const checkedSkills = specialtyList.querySelectorAll('input[type="checkbox"]:checked');

        if (!category) {
            e.preventDefault();
            showError('Please select a category.');
            return;
        }

        if (checkedSkills.length === 0) {
            e.preventDefault();
            showError('Please select at least one skill.');
            return;
        }
    });

    updateNextButton();
</script>
</body>
</html>