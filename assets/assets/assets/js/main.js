// Mobile Menu Toggle
const mobileMenuBtn = document.querySelector('.mobile-menu');
const navLinks = document.querySelector('.nav-links');

mobileMenuBtn.addEventListener('click', () => {
    navLinks.classList.toggle('active');
});

// Close mobile menu when clicking on a link
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
        navLinks.classList.remove('active');
    });
});

// Testimonial Slider
const testimonialSlider = document.querySelector('.testimonial-slider');
let isDown = false;
let startX;
let scrollLeft;

testimonialSlider.addEventListener('mousedown', (e) => {
    isDown = true;
    startX = e.pageX - testimonialSlider.offsetLeft;
    scrollLeft = testimonialSlider.scrollLeft;
    testimonialSlider.style.cursor = 'grabbing';
});

testimonialSlider.addEventListener('mouseleave', () => {
    isDown = false;
    testimonialSlider.style.cursor = 'grab';
});

testimonialSlider.addEventListener('mouseup', () => {
    isDown = false;
    testimonialSlider.style.cursor = 'grab';
});

testimonialSlider.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - testimonialSlider.offsetLeft;
    const walk = (x - startX) * 2;
    testimonialSlider.scrollLeft = scrollLeft - walk;
});

// Form Validation
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', (e) => {
        let valid = true;
        
        // Check required fields
        form.querySelectorAll('[required]').forEach(input => {
            if (!input.value.trim()) {
                valid = false;
                input.classList.add('error');
            } else {
                input.classList.remove('error');
            }
        });
        
        // Check password match
        if (form.querySelector('#password') && form.querySelector('#confirm_password')) {
            const password = form.querySelector('#password').value;
            const confirmPassword = form.querySelector('#confirm_password').value;
            
            if (password !== confirmPassword) {
                valid = false;
                form.querySelector('#confirm_password').classList.add('error');
                alert('Passwords do not match');
            } else {
                form.querySelector('#confirm_password').classList.remove('error');
            }
        }
        
        if (!valid) {
            e.preventDefault();
        }
    });
});

// Scroll to top button
const scrollToTopBtn = document.createElement('button');
scrollToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
scrollToTopBtn.className = 'scroll-to-top';
document.body.appendChild(scrollToTopBtn);

window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
        scrollToTopBtn.style.display = 'block';
    } else {
        scrollToTopBtn.style.display = 'none';
    }
});

scrollToTopBtn.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Animation on scroll
const animateOnScroll = () => {
    const elements = document.querySelectorAll('.step, .property-card, .testimonial');
    
    elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.2;
        
        if (elementPosition < screenPosition) {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }
    });
};

// Set initial state for animation
document.querySelectorAll('.step, .property-card, .testimonial').forEach(element => {
    element.style.opacity = '0';
    element.style.transform = 'translateY(20px)';
    element.style.transition = 'all 0.5s ease';
});

window.addEventListener('scroll', animateOnScroll);
window.addEventListener('load', animateOnScroll);
