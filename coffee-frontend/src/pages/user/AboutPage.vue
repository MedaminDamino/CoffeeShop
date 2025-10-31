<template>
  <div class="about-page">
    <NavBar />
    
    <!-- Hero Section with animated gradient -->
    <div class="hero-section">
      <div class="hero-overlay"></div>
      <div class="container">
        <div class="row">
          <div class="col-lg-8 mx-auto text-center hero-content">
            <h1 class="hero-title">About Coffee Shop</h1>
            <div class="hero-line"></div>
            <p class="hero-subtitle">
              Passionate about serving the finest coffee and creating memorable experiences
              for our community since 2010.
            </p>
          </div>
        </div>
      </div>
      <div class="coffee-beans"></div>
    </div>

    <!-- Story Section -->
    <div class="container story-section">
      <div class="row align-items-center">
        <div class="col-lg-6 story-content">
          <h2 class="section-title">Our Story</h2>
          <div class="title-underline"></div>
          <p class="story-text">
            Founded in 2010, Coffee Shop began as a small family-owned café with a simple mission:
            to serve exceptional coffee in a warm, welcoming environment. What started as a passion
            project has grown into a beloved community gathering place.
          </p>
          <p class="story-text">
            We source our beans directly from sustainable farms around the world, ensuring that
            every cup tells a story of quality, ethics, and craftsmanship. Our expert baristas
            are dedicated to perfecting the art of coffee-making, from the traditional espresso
            to innovative specialty drinks.
          </p>
        </div>
        <div class="col-lg-6">
          <div class="story-image-wrapper">
            <div class="story-image-card"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Values Section -->
    <div class="values-section">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="section-title">Our Values</h2>
          <div class="title-underline mx-auto"></div>
        </div>
        <div class="row g-4">
          <div class="col-md-6 col-lg-3" v-for="(value, index) in values" :key="index">
            <div class="value-card" :style="{ animationDelay: `${index * 0.1}s` }">
              <div class="value-icon-wrapper">
                <i :class="`bi ${value.icon}`"></i>
              </div>
              <h5 class="value-title">{{ value.title }}</h5>
              <p class="value-text">{{ value.text }}</p>
              <div class="value-hover-line"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-section">
      <div class="container">
        <div class="row text-center g-4">
          <div class="col-6 col-md-3" v-for="(stat, index) in stats" :key="index">
            <div class="stat-item" :style="{ animationDelay: `${index * 0.15}s` }">
              <div class="stat-number">{{ stat.number }}</div>
              <div class="stat-label">{{ stat.label }}</div>
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

const values = [
  {
    icon: 'bi-heart-fill',
    title: 'Quality',
    text: 'Only the finest beans and ingredients make it into our drinks.'
  },
  {
    icon: 'bi-people-fill',
    title: 'Community',
    text: 'Building connections and creating memories in our café.'
  },
  {
    icon: 'bi-leaf-fill',
    title: 'Sustainability',
    text: 'Committed to ethical sourcing and environmental responsibility.'
  },
  {
    icon: 'bi-star-fill',
    title: 'Excellence',
    text: 'Striving for perfection in every cup we serve.'
  }
]

const stats = [
  { number: '14+', label: 'Years' },
  { number: '50K+', label: 'Cups Served' },
  { number: '12', label: 'Countries' },
  { number: '98%', label: 'Happy Customers' }
]
</script>

<style scoped>
.about-page {
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
  background: radial-gradient(circle at 20% 50%, rgba(140, 99, 83, 0.2) 0%, transparent 50%);
  animation: pulseOverlay 8s ease-in-out infinite;
}

@keyframes pulseOverlay {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 0.8; }
}

.coffee-beans {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  background: radial-gradient(circle, rgba(231, 215, 201, 0.1) 2px, transparent 2px);
  background-size: 50px 50px;
  animation: floatBeans 20s linear infinite;
  opacity: 0.3;
}

@keyframes floatBeans {
  0% { transform: translateY(0); }
  100% { transform: translateY(-50px); }
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

/* Story Section */
.story-section {
  padding: 100px 15px;
}

.story-content {
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

.story-text {
  font-size: 1.1rem;
  color: #1A2845;
  line-height: 1.9;
  margin-bottom: 1.5rem;
  opacity: 0.9;
}

.story-image-wrapper {
  position: relative;
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

.story-image-card {
  width: 100%;
  height: 400px;
  background: linear-gradient(135deg, #8C6353 0%, #1A2845 100%);
  border-radius: 20px;
  position: relative;
  overflow: hidden;
}

.story-image-card::before {
  content: '☕';
  position: absolute;
  font-size: 120px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  opacity: 0.2;
  animation: rotateIcon 10s linear infinite;
}

@keyframes rotateIcon {
  from { transform: translate(-50%, -50%) rotate(0deg); }
  to { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Values Section */
.values-section {
  padding: 100px 15px;
  background: #E7D7C9;
  position: relative;
}

.value-card {
  background: #EEEAE4;
  border-radius: 20px;
  padding: 40px 30px;
  text-align: center;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative;
  overflow: hidden;
  height: 100%;
  animation: fadeInUp 0.8s ease-out both;
  cursor: pointer;
}

.value-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(26, 40, 69, 0.15);
}

.value-icon-wrapper {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #8C6353, #1A2845);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  transition: all 0.4s ease;
}

.value-card:hover .value-icon-wrapper {
  transform: scale(1.1) rotate(5deg);
}

.value-icon-wrapper i {
  font-size: 2rem;
  color: #E7D7C9;
}

.value-title {
  font-size: 1.4rem;
  font-weight: 700;
  color: #1A2845;
  margin-bottom: 1rem;
}

.value-text {
  font-size: 0.95rem;
  color: #1A2845;
  line-height: 1.7;
  opacity: 0.8;
  margin: 0;
}

.value-hover-line {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 4px;
  background: linear-gradient(90deg, #8C6353, #1A2845);
  transition: width 0.4s ease;
}

.value-card:hover .value-hover-line {
  width: 100%;
}

/* Stats Section */
.stats-section {
  padding: 80px 15px;
  background: #1A2845;
}

.stat-item {
  animation: fadeInUp 0.8s ease-out both;
}

.stat-number {
  font-size: 3rem;
  font-weight: 800;
  color: #E7D7C9;
  margin-bottom: 0.5rem;
  background: linear-gradient(135deg, #E7D7C9, #8C6353);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stat-label {
  font-size: 1rem;
  color: #E7D7C9;
  text-transform: uppercase;
  letter-spacing: 2px;
  opacity: 0.8;
}

/* Responsive */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .section-title {
    font-size: 2rem;
    text-align: center;
  }
  .title-underline {
  width: 60px;
  height: 3px;
  background: #8C6353;
  margin-bottom: 2rem;
  margin-left: 160px;
}

  .story-text {
  font-size: 1.1rem;
  color: #1A2845;
  line-height: 1.9;
  margin-bottom: 1.5rem;
  opacity: 0.9;
  text-align: justify;
}

  
  .story-section {
    padding: 60px 15px;
  }
  
  .values-section {
    padding: 60px 15px;
  }
  
  .stats-section {
    padding: 60px 15px;
  }
  
  .stat-number {
    font-size: 2.5rem;
  }
}
</style>