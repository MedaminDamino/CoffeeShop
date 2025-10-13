<template>
  <div class="contact-page">
    <NavBar />
    
    <!-- Hero Section -->
    <div class="hero-section">
      <div class="hero-overlay"></div>
      <div class="container">
        <div class="row">
          <div class="col-lg-8 mx-auto text-center hero-content">
            <h1 class="hero-title">Get In Touch</h1>
            <div class="hero-line"></div>
            <p class="hero-subtitle">
              We'd love to hear from you. Visit us, call us, or send us a message.
            </p>
          </div>
        </div>
      </div>
      <div class="coffee-steam"></div>
    </div>

    <!-- Contact Info & Form Section -->
    <div class="container contact-container">
      <div class="row g-5">
        <!-- Contact Information -->
        <div class="col-lg-5">
          <div class="contact-info-wrapper">
            <h2 class="section-title">Rachfa Coffee</h2>
            <div class="title-underline"></div>
            <p class="info-description">
              Visit our cozy café in the heart of the city. We're here to serve you the finest coffee experience.
            </p>

            <div class="contact-items">
              <div class="contact-item" v-for="(item, index) in contactInfo" :key="index" 
                   :style="{ animationDelay: `${index * 0.1}s` }">
                <div class="contact-icon">
                  <i :class="`bi ${item.icon}`"></i>
                </div>
                <div class="contact-details">
                  <h5 class="contact-label">{{ item.label }}</h5>
                  <p class="contact-value" v-html="item.value"></p>
                </div>
              </div>
            </div>

            <!-- Social Media -->
            <div class="social-section">
              <h5 class="social-title">Follow Us</h5>
              <div class="social-links">
                <a href="#" class="social-link" v-for="(social, index) in socials" :key="index"
                   :style="{ animationDelay: `${index * 0.1}s` }">
                  <i :class="`bi ${social.icon}`"></i>
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-7">
          <div class="contact-form-wrapper">
            <h3 class="form-title">Send Us a Message</h3>
            <div class="title-underline"></div>
            
            <form class="contact-form">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Your Name</label>
                    <input type="text" class="form-control" placeholder="John Doe">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label">Your Email</label>
                    <input type="email" class="form-control" placeholder="john@example.com">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" placeholder="+216 XX XXX XXX">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" class="form-control" placeholder="How can we help you?">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" rows="5" placeholder="Tell us more about your inquiry..."></textarea>
                  </div>
                </div>
                <div class="col-12">
                  <button type="submit" class="submit-btn">
                    <span>Send Message</span>
                    <i class="bi bi-arrow-right"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Hours Section -->
    <div class="hours-section">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="section-title">Opening Hours</h2>
          <div class="title-underline mx-auto"></div>
        </div>
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="hours-card">
              <div class="row g-0">
                <div class="col-md-6" v-for="(hour, index) in hours" :key="index">
                  <div class="hour-item" :style="{ animationDelay: `${index * 0.1}s` }">
                    <div class="day">{{ hour.day }}</div>
                    <div class="time">{{ hour.time }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Map Section -->
    <div class="map-section">
      <div class="container-fluid p-0">
        <div class="map-placeholder">
          <div class="map-overlay">
            <div class="map-info">
              <i class="bi bi-geo-alt-fill"></i>
              <h4>Find Us Here</h4>
              <p>Avenue Habib Bourguiba, Tunis 1000, Tunisia</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <AppFooter />
  </div>
</template>

<script setup lang="ts">
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const contactInfo = [
  {
    icon: 'bi-geo-alt-fill',
    label: 'Address',
    value: '123 Avenue Habib Bourguiba<br>Tunis 1000, Tunisia'
  },
  {
    icon: 'bi-telephone-fill',
    label: 'Phone',
    value: '+216 71 234 567<br>+216 98 765 432'
  },
  {
    icon: 'bi-envelope-fill',
    label: 'Email',
    value: 'hello@rachfacoffee.tn<br>info@rachfacoffee.tn'
  },
  {
    icon: 'bi-clock-fill',
    label: 'Quick Contact',
    value: 'Mon - Fri: 7:00 AM - 10:00 PM<br>Weekends: 8:00 AM - 11:00 PM'
  }
]

const socials = [
  { icon: 'bi-facebook', url: '#' },
  { icon: 'bi-instagram', url: '#' },
  { icon: 'bi-twitter', url: '#' },
  { icon: 'bi-linkedin', url: '#' }
]

const hours = [
  { day: 'Monday - Friday', time: '7:00 AM - 10:00 PM' },
  { day: 'Saturday', time: '8:00 AM - 11:00 PM' },
  { day: 'Sunday', time: '8:00 AM - 11:00 PM' },
  { day: 'Public Holidays', time: '9:00 AM - 9:00 PM' }
]
</script>

<style scoped>
.contact-page {
  min-height: 100vh;
  background: #EEEAE4;
}

/* Hero Section */
.hero-section {
  position: relative;
  background: linear-gradient(135deg, #1A2845 0%, #2d3f5f 100%);
  padding: 120px 0 100px;
  overflow: hidden;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle at 80% 20%, rgba(140, 99, 83, 0.2) 0%, transparent 50%);
  animation: pulseOverlay 8s ease-in-out infinite;
}

@keyframes pulseOverlay {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 0.8; }
}

.coffee-steam {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  background: 
    radial-gradient(ellipse at 30% 40%, rgba(231, 215, 201, 0.1) 0%, transparent 30%),
    radial-gradient(ellipse at 70% 60%, rgba(231, 215, 201, 0.08) 0%, transparent 35%);
  animation: floatSteam 15s ease-in-out infinite;
}

@keyframes floatSteam {
  0%, 100% { transform: translateY(0) scale(1); opacity: 0.3; }
  50% { transform: translateY(-20px) scale(1.1); opacity: 0.5; }
}

.hero-content {
  position: relative;
  z-index: 2;
  animation: fadeInUp 1s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 800;
  color: #E7D7C9;
  margin-bottom: 1.5rem;
  letter-spacing: -1px;
}

.hero-line {
  width: 80px;
  height: 4px;
  background: linear-gradient(90deg, #8C6353, #E7D7C9);
  margin: 0 auto 2rem;
  animation: expandLine 1.5s ease-out;
}

@keyframes expandLine {
  from { width: 0; }
  to { width: 80px; }
}

.hero-subtitle {
  font-size: 1.25rem;
  color: #E7D7C9;
  line-height: 1.8;
  opacity: 0;
  animation: fadeIn 1s ease-out 0.5s forwards;
}

@keyframes fadeIn {
  to { opacity: 1; }
}

/* Contact Container */
.contact-container {
  padding: 100px 15px;
}

/* Contact Info */
.contact-info-wrapper {
  animation: slideInLeft 1s ease-out;
}

@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.section-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1A2845;
  margin-bottom: 1rem;
}

.title-underline {
  width: 60px;
  height: 3px;
  background: #8C6353;
  margin-bottom: 2rem;
}

.info-description {
  font-size: 1.1rem;
  color: #1A2845;
  line-height: 1.8;
  margin-bottom: 3rem;
  opacity: 0.8;
}

.contact-items {
  margin-bottom: 3rem;
}

.contact-item {
  display: flex;
  align-items: flex-start;
  gap: 1.5rem;
  margin-bottom: 2rem;
  animation: fadeInUp 0.8s ease-out both;
  transition: transform 0.3s ease;
}

.contact-item:hover {
  transform: translateX(5px);
}

.contact-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #8C6353, #1A2845);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.contact-item:hover .contact-icon {
  transform: scale(1.1) rotate(5deg);
}

.contact-icon i {
  font-size: 1.5rem;
  color: #E7D7C9;
}

.contact-label {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1A2845;
  margin-bottom: 0.5rem;
}

.contact-value {
  font-size: 0.95rem;
  color: #1A2845;
  opacity: 0.8;
  line-height: 1.6;
  margin: 0;
}

/* Social Section */
.social-section {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 2px solid rgba(26, 40, 69, 0.1);
}

.social-title {
  font-size: 1.2rem;
  font-weight: 600;
  color: #1A2845;
  margin-bottom: 1.5rem;
}

.social-links {
  display: flex;
  gap: 1rem;
}

.social-link {
  width: 50px;
  height: 50px;
  background: #E7D7C9;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1A2845;
  font-size: 1.3rem;
  transition: all 0.3s ease;
  animation: fadeInUp 0.8s ease-out both;
  text-decoration: none;
}

.social-link:hover {
  background: linear-gradient(135deg, #8C6353, #1A2845);
  color: #E7D7C9;
  transform: translateY(-5px) rotate(5deg);
}

/* Contact Form */
.contact-form-wrapper {
  background: white;
  padding: 50px;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(26, 40, 69, 0.08);
  animation: slideInRight 1s ease-out;
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.form-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1A2845;
  margin-bottom: 1rem;
}

.form-group {
  margin-bottom: 0;
}

.form-label {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1A2845;
  margin-bottom: 0.5rem;
  display: block;
}

.form-control {
  width: 100%;
  padding: 14px 20px;
  border: 2px solid #E7D7C9;
  border-radius: 12px;
  font-size: 1rem;
  color: #1A2845;
  background: #EEEAE4;
  transition: all 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #8C6353;
  background: white;
  box-shadow: 0 0 0 4px rgba(140, 99, 83, 0.1);
}

.form-control::placeholder {
  color: rgba(26, 40, 69, 0.4);
}

textarea.form-control {
  resize: vertical;
  min-height: 120px;
}

.submit-btn {
  background: linear-gradient(135deg, #8C6353, #1A2845);
  color: #E7D7C9;
  border: none;
  padding: 16px 40px;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.submit-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(26, 40, 69, 0.3);
}

.submit-btn i {
  transition: transform 0.3s ease;
}

.submit-btn:hover i {
  transform: translateX(5px);
}

/* Hours Section */
.hours-section {
  padding: 100px 15px;
  background: #E7D7C9;
}

.hours-card {
  background: #EEEAE4;
  border-radius: 20px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(26, 40, 69, 0.08);
}

.hour-item {
  padding: 30px;
  border-bottom: 1px solid rgba(26, 40, 69, 0.1);
  animation: fadeInUp 0.8s ease-out both;
  transition: all 0.3s ease;
}

.hour-item:hover {
  background: rgba(140, 99, 83, 0.05);
  transform: translateX(10px);
}

.hour-item:last-child {
  border-bottom: none;
}

.day {
  font-size: 1.2rem;
  font-weight: 600;
  color: #1A2845;
  margin-bottom: 0.5rem;
}

.time {
  font-size: 1rem;
  color: #8C6353;
  font-weight: 500;
}

/* Map Section */
.map-section {
  margin-top: 100px;
}

.map-placeholder {
  width: 100%;
  height: 500px;
  background: linear-gradient(135deg, #1A2845 0%, #8C6353 100%);
  position: relative;
  overflow: hidden;
}

.map-placeholder::before {
  content: '';
  position: absolute;
  width: 100%;
  height: 100%;
  background: 
    repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(231, 215, 201, 0.05) 10px, rgba(231, 215, 201, 0.05) 20px);
  animation: movePattern 20s linear infinite;
}

@keyframes movePattern {
  0% { transform: translateX(0); }
  100% { transform: translateX(40px); }
}

.map-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(26, 40, 69, 0.8);
}

.map-info {
  text-align: center;
  color: #E7D7C9;
  animation: fadeInUp 1s ease-out;
}

.map-info i {
  font-size: 4rem;
  margin-bottom: 1rem;
  color: #8C6353;
  animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-15px); }
}

.map-info h4 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.map-info p {
  font-size: 1.2rem;
  opacity: 0.9;
  margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .contact-container {
    padding: 60px 15px;
  }
  
  .contact-form-wrapper {
    padding: 30px 20px;
  }
  
  .hours-section {
    padding: 60px 15px;
  }
  
  .hours-card {
    padding: 20px;
  }
  
  .hour-item {
    padding: 20px;
  }
  
  .map-placeholder {
    height: 400px;
  }
  
  .map-info h4 {
    font-size: 1.5rem;
  }
  
  .map-info i {
    font-size: 3rem;
  }
}
</style>