document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.slider');
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    let currentSlide = 0;
    let slideInterval;

    // Function to show specific slide
    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        slides[index].classList.add('active');
        indicators[index].classList.add('active');
        currentSlide = index;
    }

    // Next slide function
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    // Previous slide function
    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }

    // Event listeners
    prevBtn.addEventListener('click', () => {
        prevSlide();
        resetInterval();
    });

    nextBtn.addEventListener('click', () => {
        nextSlide();
        resetInterval();
    });

    // Indicator clicks
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            showSlide(index);
            resetInterval();
        });
    });

    // Auto slide function
    function startSlideShow() {
        slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
    }

    // Reset interval function
    function resetInterval() {
        clearInterval(slideInterval);
        startSlideShow();
    }

    // Start the slideshow
    startSlideShow();
});