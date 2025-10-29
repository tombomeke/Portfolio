// /public/js/script.js
document.addEventListener('DOMContentLoaded', function() {
    // Mobile Navigation Toggle
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');

            // Animate hamburger menu
            const bars = navToggle.querySelectorAll('.bar');
            bars.forEach((bar, index) => {
                if (navMenu.classList.contains('active')) {
                    if (index === 0) bar.style.transform = 'rotate(45deg) translate(5px, 5px)';
                    if (index === 1) bar.style.opacity = '0';
                    if (index === 2) bar.style.transform = 'rotate(-45deg) translate(7px, -6px)';
                } else {
                    bar.style.transform = 'none';
                    bar.style.opacity = '1';
                }
            });
        });
    }

    // Close mobile menu when clicking on a link
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navMenu && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                const bars = document.querySelectorAll('.bar');
                bars.forEach(bar => {
                    bar.style.transform = 'none';
                    bar.style.opacity = '1';
                });
            }
        });
    });

    // Games Tab Functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    // Debug: Check if elements are found
    console.log('Tab buttons found:', tabBtns.length);
    console.log('Tab contents found:', tabContents.length);

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all buttons and contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            const targetContent = document.getElementById(targetTab);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });

// Project Filtering
    const filterBtns = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-card');

    // Only initialize if elements exist
    if (filterBtns.length > 0 && projectCards.length > 0) {
        console.log('Filter buttons found:', filterBtns.length);
        console.log('Project cards found:', projectCards.length);

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const filterValue = this.getAttribute('data-filter');

                // Update active filter button
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Filter projects with improved animation
                projectCards.forEach((card, index) => {
                    const category = card.getAttribute('data-category');

                    if (filterValue === 'all' || category === filterValue) {
                        // Show card with staggered animation
                        setTimeout(() => {
                            card.style.display = 'block';
                            card.style.opacity = '0';
                            card.style.transform = 'translateY(30px) scale(0.95)';
                            card.style.transition = 'all 0.5s ease';

                            requestAnimationFrame(() => {
                                card.style.opacity = '1';
                                card.style.transform = 'translateY(0) scale(1)';
                            });
                        }, index * 100); // Stagger the animations
                    } else {
                        // Hide card with fade out
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(-20px) scale(0.95)';
                        card.style.transition = 'all 0.3s ease';
                        
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
    }

    // Skills Progress Bar Animation with improved timing
    const skillCards = document.querySelectorAll('.skill-card');
    const skillProgressBars = document.querySelectorAll('.skill-progress .progress-bar');
    
    if (skillProgressBars.length > 0) {
        console.log('Skill cards found:', skillCards.length);
        console.log('Progress bars found:', skillProgressBars.length);
        
        // Log all progress bars and their data-width attributes
        skillProgressBars.forEach((bar, index) => {
            const targetWidth = bar.getAttribute('data-width');
            console.log(`Progress bar ${index + 1}: data-width = "${targetWidth}"`);
        });

        function animateSkillBars() {
            console.log('Starting skill bar animations...');
            skillProgressBars.forEach((bar, index) => {
                const targetWidth = bar.getAttribute('data-width');
                console.log(`Processing bar ${index + 1}: targetWidth = "${targetWidth}"`);

                if (targetWidth) {
                    const skillCard = bar.closest('.skill-card');
                    if (skillCard) {
                        // Check if already animated
                        if (bar.classList.contains('animated')) {
                            console.log(`Bar ${index + 1} already animated, skipping`);
                            return;
                        }

                        // Mark as animated immediately to prevent re-animation
                        bar.classList.add('animated');

                        // Reset width to 0 first
                        bar.style.width = '0%';

                        // Use longer delay to ensure rendering is complete
                        setTimeout(() => {
                            // Add animate class and set target width
                            skillCard.classList.add('animate');
                            bar.style.width = targetWidth;

                            console.log(`Animated bar ${index + 1}: set width to ${targetWidth}`);
                        }, 300 + (index * 150)); // Increased initial delay and stagger
                    } else {
                        console.log(`Could not find skill card for bar ${index + 1}`);
                    }
                } else {
                    console.log(`No data-width found for bar ${index + 1}`);
                }
            });
        }

        // Use Intersection Observer - ONLY ONCE
        const skillsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    console.log('Skills section in view, starting animations');
                    // Add delay to ensure styles are fully applied
                    setTimeout(() => {
                        animateSkillBars();
                    }, 200);
                    // Disconnect observer completely to prevent re-triggering
                    skillsObserver.disconnect();
                }
            });
        }, {
            threshold: 0.2, // Trigger earlier
            rootMargin: '50px' // Start animation slightly before element is in view
        });
        
        const skillsSection = document.querySelector('.skills-section');
        if (skillsSection) {
            console.log('Observing skills section');
            skillsObserver.observe(skillsSection);
        } else {
            console.log('Skills section not found');
        }
    } else {
        console.log('No progress bars found');
    }

    // Smooth Scrolling for Internal Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Form Validation Enhancement
    const contactForm = document.querySelector('form');
    if (contactForm) {
        const inputs = contactForm.querySelectorAll('input, textarea');

        inputs.forEach(input => {
            // Add real-time validation feedback
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
                // Remove error styling when user starts typing
                this.classList.remove('error');
                const errorMsg = this.parentNode.querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.remove();
                }
            });
        });

        contactForm.addEventListener('submit', function(e) {
            let isValid = true;

            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = contactForm.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }

    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Remove existing error message
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Check if field is required and empty
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'Dit veld is verplicht';
        }

        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Voer een geldig e-mailadres in';
            }
        }

        // Name validation (at least 2 characters)
        if (field.name === 'name' && value && value.length < 2) {
            isValid = false;
            errorMessage = 'Naam moet minimaal 2 karakters bevatten';
        }

        // Message validation (at least 10 characters)
        if (field.name === 'message' && value && value.length < 10) {
            isValid = false;
            errorMessage = 'Bericht moet minimaal 10 karakters bevatten';
        }

        // Apply error styling
        if (!isValid) {
            field.classList.add('error');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = errorMessage;
            errorDiv.style.color = 'var(--error-color)';
            errorDiv.style.fontSize = '0.8rem';
            errorDiv.style.marginTop = '0.25rem';
            field.parentNode.appendChild(errorDiv);
        } else {
            field.classList.remove('error');
        }

        return isValid;
    }

    // Intersection Observer for Animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);

    // Observe elements for animation
    const animateElements = document.querySelectorAll('.skill-card, .project-card, .stat-item');
    animateElements.forEach(el => {
        observer.observe(el);
    });

    // Navbar Active Link Highlighting
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'home';
    const navLinksArray = Array.from(navLinks);

    navLinksArray.forEach(link => {
        const href = link.getAttribute('href');
        const linkPage = new URLSearchParams(href.split('?')[1] || '').get('page') || 'home';

        if (linkPage === currentPage) {
            link.style.color = 'var(--primary-color)';
            link.style.background = 'rgba(59, 130, 246, 0.1)';
        }
    });

    // Copy to Clipboard Functionality (for contact info)
    const copyableElements = document.querySelectorAll('[data-copy]');
    copyableElements.forEach(element => {
        element.style.cursor = 'pointer';
        element.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-copy') || this.textContent;

            navigator.clipboard.writeText(textToCopy).then(() => {
                // Show success feedback
                const originalText = this.textContent;
                this.textContent = 'Gekopieerd!';
                this.style.color = 'var(--success-color)';

                setTimeout(() => {
                    this.textContent = originalText;
                    this.style.color = '';
                }, 2000);
            }).catch(err => {
                console.error('Kon niet kopiëren: ', err);
            });
        });
    });

    // Loading States for External Links
    const externalLinks = document.querySelectorAll('a[target="_blank"]');
    externalLinks.forEach(link => {
        link.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Openen...';

            setTimeout(() => {
                this.innerHTML = originalText;
            }, 2000);
        });
    });

    // Stats Counter Animation (for game stats)
    function animateCounter(element, target, duration = 2000) {
        const start = parseInt(element.textContent) || 0;
        const increment = (target - start) / (duration / 16);
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 16);
    }

    const statNumbers = document.querySelectorAll('.stat-number');
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                const element = entry.target;
                const text = element.textContent;
                const numbers = text.match(/\d+/);

                if (numbers && numbers.length > 0) {
                    const target = parseInt(numbers[0]);
                    element.classList.add('animated');
                    animateCounter(element, target);
                }
            }
        });
    }, observerOptions);

    statNumbers.forEach(stat => {
        statsObserver.observe(stat);
    });

    // Improved scroll behavior and performance
    let ticking = false;
    
    function updateScrollEffects() {
        const scrolled = window.pageYOffset;
        const navbar = document.querySelector('.navbar');
        
        // Add/remove scrolled class for navbar styling
        if (scrolled > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
        
        ticking = false;
    }
    
    function requestScrollUpdate() {
        if (!ticking) {
            requestAnimationFrame(updateScrollEffects);
            ticking = true;
        }
    }
    
    window.addEventListener('scroll', requestScrollUpdate, { passive: true });
    
    // Improved project page scroll behavior
    const projectsSection = document.querySelector('.projects');
    if (projectsSection) {
        // Ensure proper scrolling on project page
        projectsSection.style.minHeight = '100vh';
        projectsSection.style.display = 'block';
        
        // Fix any potential overflow issues
        const projectsGrid = projectsSection.querySelector('.projects-grid');
        if (projectsGrid) {
            projectsGrid.style.overflow = 'visible';
            projectsGrid.style.display = 'flex';
            projectsGrid.style.flexWrap = 'wrap';
            projectsGrid.style.justifyContent = 'center';
            projectsGrid.style.gap = getComputedStyle(projectsGrid).gap || '2rem';
        }
    }
    
    // Better mobile navigation behavior (using existing navToggle and navMenu variables)
    if (navToggle && navMenu) {
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                const bars = document.querySelectorAll('.bar');
                bars.forEach(bar => {
                    bar.style.transform = 'none';
                    bar.style.opacity = '1';
                });
            }
        });
        
        // Prevent body scroll when mobile menu is open
        navToggle.addEventListener('click', function() {
            if (navMenu.classList.contains('active')) {
                document.body.style.overflow = '';
            } else {
                document.body.style.overflow = 'hidden';
            }
        });
        
        // Restore body scroll when menu is closed
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                document.body.style.overflow = '';
            });
        });
    }
    
    // Performance optimization: Debounce resize events
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            // Recalculate layouts if needed
            const projectsGrid = document.querySelector('.projects-grid');
            if (projectsGrid) {
                projectsGrid.style.display = 'none';
                requestAnimationFrame(() => {
                    projectsGrid.style.display = 'flex';
                    projectsGrid.style.flexWrap = 'wrap';
                    projectsGrid.style.justifyContent = 'center';
                });
            }
        }, 250);
    });
    
    // Note: externalLinks already declared above, no need to redeclare
    
    // Improved form validation feedback
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea');
        
        inputs.forEach(input => {
            // Real-time validation feedback
            input.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.classList.remove('error');
                    this.classList.add('valid');
                } else {
                    this.classList.remove('valid');
                }
            });
            
            // Better focus management
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    });
});

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    .skill-card,
    .project-card,
    .stat-item {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease;
    }
    
    .animate-in {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
    
    .form-group input.error,
    .form-group textarea.error {
        border-color: var(--error-color);
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.1);
    }
    
    .form-group input.valid,
    .form-group textarea.valid {
        border-color: var(--success-color);
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.1);
    }
    
    .form-group.focused {
        transform: translateY(-2px);
    }
    
    .navbar.scrolled {
        background: rgba(15, 23, 42, 0.98);
        backdrop-filter: blur(15px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
            @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }
`;
document.head.appendChild(style);