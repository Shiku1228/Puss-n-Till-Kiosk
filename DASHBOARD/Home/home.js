document.addEventListener('DOMContentLoaded', function () {
    const toggleImagesContainer = document.querySelector('.toggle-images');
    const images = toggleImagesContainer.querySelectorAll('img');

    // Function to reset images to default state
    function resetImages() {
        images.forEach(img => {
            img.classList.remove('active-img', 'shrink');
            img.style.order = '';
            const container = img.parentElement;
            const description = container.querySelector('.description');
            if (description) {
                description.classList.remove('show');
            }
        });
    }

    images.forEach(img => {
        img.addEventListener('click', function (e) {
            const container = img.parentElement;
            const description = container.querySelector('.description');
            const isActive = img.classList.contains('active-img');

            if (isActive) {
                // Hide all
                resetImages();
            } else {
                // Hide all first
                resetImages();
                // Activate this one
                img.classList.add('active-img');
                if (description) {
                    description.classList.add('show');
                }
                // Shrink the other images
                images.forEach(otherImg => {
                    if (otherImg !== img) {
                        otherImg.classList.add('shrink');
                    }
                });
            }
            e.stopPropagation();
        });
    });

    // Hide all when clicking outside
    document.addEventListener('click', function () {
        resetImages();
    });

    // Prevent closing when clicking inside the description
    document.querySelectorAll('.image-container .description').forEach(function (desc) {
        desc.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });
});
