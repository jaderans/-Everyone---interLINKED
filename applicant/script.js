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

function renderSpecialties(category) {
    specialtyList.innerHTML = '';
    (specialtiesData[category] || []).forEach(name => {
        const li = document.createElement('li');
        li.innerHTML = `<label><input type="checkbox"> ${name}</label>`;
        specialtyList.appendChild(li);
    });
}

categoryList.addEventListener('click', function(e) {
    if (e.target.tagName === 'LI') {
        // Update selected state
        Array.from(categoryList.children).forEach(li => li.classList.remove('selected'));
        e.target.classList.add('selected');
        // Render specialties
        renderSpecialties(e.target.textContent);
    }
});

// Initial render
renderSpecialties(document.querySelector('.category-list .selected').textContent);

// Clear selections
clearSelections.addEventListener('click', function(e) {
    e.preventDefault();
    specialtyList.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
});
