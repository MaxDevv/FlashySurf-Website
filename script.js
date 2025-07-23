
document.addEventListener('DOMContentLoaded', function() {
    // --- "Alive" Scroll Animation Functionality ---
    const animatedItems = document.querySelectorAll('.animated-item');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    animatedItems.forEach(item => { observer.observe(item); });

    // --- Slideshow Functionality ---
    const track = document.querySelector('.slideshow-track');
    if (track) {
        const slides = Array.from(track.children);
        const nextButton = document.querySelector('.next-btn');
        const prevButton = document.querySelector('.prev-btn');
        const dotsNav = document.querySelector('.slideshow-dots');
        let currentIndex = 0;

        let moveToSlide = (targetIndex) => {
            track.style.transform = 'translateX(-' + 100 * targetIndex + '%)';
            dots[currentIndex].classList.remove('active');
            dots[targetIndex].classList.add('active');
            currentIndex = targetIndex;
        };
        
        slides.forEach((_, index) => {
            const dot = document.createElement('button'); dot.classList.add('dot');
            if (index === 0) dot.classList.add('active');
            dot.addEventListener('click', () => moveToSlide(index));
            dotsNav.appendChild(dot);
        });
        const dots = Array.from(dotsNav.children);

        prevButton.addEventListener('click', () => { moveToSlide((currentIndex === 0) ? slides.length - 1 : currentIndex - 1); });
        nextButton.addEventListener('click', () => { moveToSlide((currentIndex === slides.length - 1) ? 0 : currentIndex + 1); });
    }

    // *temporary carousel fix*
    track.classList.add('notransition');
    for (let index = 0; index < 4; index++) {
        document.querySelector("button.slide-btn.next-btn").click();
    }
    track.offsetHeight;
    track.classList.remove('notransition');

    // --- Interactive Feature List Functionality ---
    const featureButtons = document.querySelectorAll('.feature-list-btn');
    const featureImage = document.getElementById('feature-display-image');
    if (featureButtons.length > 0 && featureImage) {
        featureButtons.forEach(button => {
            button.addEventListener('click', () => {
                featureButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                const newImageSrc = button.getAttribute('data-image-src');
                featureImage.style.opacity = '0';
                setTimeout(() => {
                    featureImage.src = newImageSrc;
                    featureImage.style.opacity = '1';
                }, 200);
            });
        });
    }

    // Bugfixes

});