<template>
  <footer class="footer-section">
    <!-- Main Footer Content -->
    <div class="footer-main py-5">
      <div class="container">
        <div class="row g-5">
          <!-- Brand Section -->
          <div class="col-lg-4 col-md-6">
            <div class="footer-brand mb-4">
              <h3 class="brand-name mb-3">RACHFA</h3>
              <p class="brand-tagline mb-4">
                Crafting exceptional coffee experiences since 2020.
                Every cup tells a story, every sip is a journey.
              </p>
              <!-- Newsletter -->
              <div class="newsletter-box">
                <h6 class="fw-semibold mb-3">Stay Updated</h6>
                <div class="input-group newsletter-input">
                  <input 
                    type="email" 
                    class="form-control" 
                    placeholder="Enter your email"
                    aria-label="Email address"
                  />
                  <button class="btn btn-subscribe" type="button">
                    <span>Subscribe</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Links -->
          <div class="col-lg-2 col-md-3 col-6">
            <h6 class="footer-title mb-4">About</h6>
            <ul class="footer-links list-unstyled">
              <li class="mb-3">
                <router-link to="/about" class="footer-link">
                  <span class="link-text">Our Story</span>
                </router-link>
              </li>
              <li class="mb-3">
                <router-link to="/menu" class="footer-link">
                  <span class="link-text">Menu</span>
                </router-link>
              </li>
              <li class="mb-3">
                <router-link to="/contact" class="footer-link">
                  <span class="link-text">Locations</span>
                </router-link>
              </li>
              <li class="mb-3">
                <router-link to="/about" class="footer-link">
                  <span class="link-text">Careers</span>
                </router-link>
              </li>
            </ul>
          </div>

          <!-- Support Links -->
          <div class="col-lg-2 col-md-3 col-6">
            <h6 class="footer-title mb-4">Support</h6>
            <ul class="footer-links list-unstyled">
              <li class="mb-3">
                <router-link to="/contact" class="footer-link">
                  <span class="link-text">Contact Us</span>
                </router-link>
              </li>
              <li class="mb-3">
                <router-link to="#" class="footer-link">
                  <span class="link-text">FAQ</span>
                </router-link>
              </li>
              <li class="mb-3">
                <router-link to="/menu" class="footer-link">
                  <span class="link-text">Shipping Info</span>
                </router-link>
              </li>
              <li class="mb-3">
                <router-link to="#" class="footer-link">
                  <span class="link-text">Returns</span>
                </router-link>
              </li>
            </ul>
          </div>

          <!-- Contact & Social -->
          <div class="col-lg-4 col-md-6">
            <h6 class="footer-title mb-4">Connect With Us</h6>
            <div class="contact-info mb-4">
              <p class="contact-item mb-2">
                <span class="contact-icon">📧</span>
                <span>hello@rachfa.coffee</span>
              </p>
              <p class="contact-item mb-2">
                <span class="contact-icon">📞</span>
                <span>+216 71 234 567</span>
              </p>
              <p class="contact-item mb-0">
                <span class="contact-icon">📍</span>
                <span>123 Avenue Habib Bourguiba, Tunisia</span>
              </p>
            </div>
            
            <!-- Social Media -->
            <div class="social-links">
              <a href="#" class="social-link" aria-label="Facebook">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                </svg>
              </a>
              <a href="#" class="social-link" aria-label="Instagram">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                  <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                  <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                </svg>
              </a>
              <a href="#" class="social-link" aria-label="Twitter">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                </svg>
              </a>
              <a href="#" class="social-link" aria-label="LinkedIn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                  <rect x="2" y="9" width="4" height="12"></rect>
                  <circle cx="4" cy="4" r="2"></circle>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom py-4">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
            <p class="mb-0 copyright-text">
              &copy; 2025 RACHFA. All rights reserved.
            </p>
          </div>
          <div class="col-md-6">
            <ul class="footer-legal list-unstyled d-flex justify-content-center justify-content-md-end mb-0 gap-4">
              <li>
                <router-link to="#" class="legal-link">Privacy Policy</router-link>
              </li>
              <li>
                <router-link to="#" class="legal-link">Terms of Service</router-link>
              </li>
              <li>
                <router-link to="#" class="legal-link">Cookies</router-link>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
// Modern footer component with enhanced UX
</script>

<style scoped>
/* Main Footer Styling */
.footer-section {
  background: linear-gradient(180deg, #faf8f5 0%, #f5f2ed 100%);
  color: #4a4a4a;
}

.footer-main {
  border-bottom: 1px solid rgba(74, 74, 74, 0.1);
}

/* Brand Section */
.brand-name {
  font-size: 2rem;
  font-weight: 700;
  color: #2c2c2c;
  letter-spacing: 1px;
  margin-bottom: 0;
}

.brand-tagline {
  color: #6b6b6b;
  line-height: 1.6;
  font-size: 0.95rem;
}

/* Newsletter */
.newsletter-box h6 {
  color: #2c2c2c;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.newsletter-input {
  border-radius: 50px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.newsletter-input .form-control {
  border: none;
  padding: 0.75rem 1.25rem;
  background: white;
  color: #4a4a4a;
  font-size: 0.9rem;
}

.newsletter-input .form-control:focus {
  box-shadow: none;
  outline: none;
}

.newsletter-input .form-control::placeholder {
  color: #999;
}

.btn-subscribe {
  background: #4a4a4a;
  color: white;
  border: none;
  padding: 0.75rem 1.75rem;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.btn-subscribe:hover {
  background: #2c2c2c;
  transform: translateX(-2px);
}

/* Footer Titles */
.footer-title {
  color: #2c2c2c;
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  position: relative;
  padding-bottom: 0.5rem;
}

.footer-title::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: 0;
  width: 30px;
  height: 2px;
  background: #4a4a4a;
}

/* Footer Links */
.footer-links {
  margin: 0;
  padding: 0;
}

.footer-link {
  color: #6b6b6b;
  text-decoration: none;
  display: inline-block;
  position: relative;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.footer-link .link-text {
  position: relative;
}

.footer-link .link-text::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -2px;
  width: 0;
  height: 1px;
  background: #4a4a4a;
  transition: width 0.3s ease;
}

.footer-link:hover {
  color: #2c2c2c;
  transform: translateX(4px);
}

.footer-link:hover .link-text::after {
  width: 100%;
}

/* Contact Info */
.contact-info {
  color: #6b6b6b;
  font-size: 0.95rem;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.contact-icon {
  font-size: 1.1rem;
  min-width: 24px;
}

/* Social Links */
.social-links {
  display: flex;
  gap: 1rem;
}

.social-link {
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  color: #4a4a4a;
  border-radius: 50%;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.social-link:hover {
  background: #4a4a4a;
  color: white;
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Footer Bottom */
.footer-bottom {
  background: rgba(74, 74, 74, 0.03);
}

.copyright-text {
  color: #6b6b6b;
  font-size: 0.9rem;
}

.footer-legal {
  flex-wrap: wrap;
}

.legal-link {
  color: #6b6b6b;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.3s ease;
}

.legal-link:hover {
  color: #2c2c2c;
}

/* Responsive Design */
@media (max-width: 768px) {
  .brand-name {
    font-size: 1.75rem;
  }

  .footer-title::after {
    width: 25px;
  }

  .social-links {
    justify-content: start;
  }

  .newsletter-input .btn-subscribe {
    padding: 0.75rem 1.25rem;
  }
}

@media (max-width: 576px) {
  .footer-legal {
    gap: 1rem !important;
    font-size: 0.85rem;
  }
}
</style>