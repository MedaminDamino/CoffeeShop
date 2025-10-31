<template>
  <div class="coffee-homepage">
    <NavBar />

    <!-- Hero Section -->
    <section class="hero-section position-relative">
      <div class="container h-100">
        <div class="row h-100 align-items-center">
          <div class="col-lg-6">
            <div class="hero-content">
              <p class="text-uppercase mb-3 tracking-wide text-muted">Premium Coffee Experience</p>
              <h1 class="display-1 fw-bold mb-4 hero-title">
                Crafted<br />
                With<br />
                Passion
              </h1>
              <p class="lead mb-5 text-muted">
                Discover the finest single-origin beans, expertly roasted and brewed to perfection. Every cup tells a
                story.
              </p>
              <div class="d-flex gap-3 flex-wrap">
                <router-link class="btn btn-dark btn-lg px-5 py-3" to="menu">
                  Explore Menu
                </router-link>
                <button class="btn btn-outline-dark btn-lg px-5 py-3">
                  Our Story
                </button>
              </div>
            </div>
          </div>
          <div class="col-lg-6 d-md-block mt-3">
            <div class="hero-image-container">
              <img src="/img1.jpg" alt="Premium Coffee" class="img-fluid rounded-4 shadow-lg" />
            </div>
          </div>
        </div>  
      </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5">
      <div class="container py-5">
        <div class="row g-4">
          <div class="col-md-4">
            <div class="feature-card p-5 h-100">
              <div class="feature-icon mb-4">
                <Coffee :size="48" />
              </div>
              <h3 class="h4 fw-bold mb-3">Single Origin</h3>
              <p class="text-muted mb-0">
                Carefully sourced beans from the world's finest coffee regions, ensuring exceptional quality and unique
                flavor profiles.
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-card p-5 h-100">
              <div class="feature-icon mb-4">
                <Flame :size="48" />
              </div>
              <h3 class="h4 fw-bold mb-3">Fresh Roasted</h3>
              <p class="text-muted mb-0">
                Roasted in small batches daily to guarantee peak freshness and bring out the natural complexity of each
                bean.
              </p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-card p-5 h-100">
              <div class="feature-icon mb-4">
                <Heart :size="48" />
              </div>
              <h3 class="h4 fw-bold mb-3">Made with Love</h3>
              <p class="text-muted mb-0">
                Every cup is crafted by skilled baristas who are passionate about delivering the perfect coffee
                experience.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Signature Blend Section -->
    <section class="signature-section py-5">
      <div class="container py-5">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <img src="/img3.jpg" alt="Coffee Roasting" class="img-fluid rounded-4 shadow" />
          </div>
          <div class="col-lg-6">
            <p class="text-uppercase mb-3 text-muted tracking-wide">Our Signature</p>
            <h2 class="display-4 fw-bold mb-4 hero-subtitle">The Perfect Blend</h2>
            <p class="lead text-muted mb-4">
              Our master roasters have spent years perfecting a blend that balances rich chocolate notes with bright
              citrus undertones, creating a harmonious cup that delights the senses.
            </p>
            <ul class="list-unstyled mb-5">
              <li class="mb-3 d-flex align-items-start">
                <Check :size="24" class="text-success me-3 mt-1 flex-shrink-0" />
                <span>Ethically sourced from sustainable farms</span>
              </li>
              <li class="mb-3 d-flex align-items-start">
                <Check :size="24" class="text-success me-3 mt-1 flex-shrink-0" />
                <span>Roasted to order for maximum freshness</span>
              </li>
              <li class="mb-3 d-flex align-items-start">
                <Check :size="24" class="text-success me-3 mt-1 flex-shrink-0" />
                <span>Award-winning flavor profile</span>
              </li>
            </ul>
            <router-link class="btn btn-dark btn-lg px-5 py-3" to="menu">
              Shop Now
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5">
      <div class="container py-5">
        <div class="cta-card text-center p-5 rounded-4">
          <h2 class="display-5 fw-bold mb-4">We’d Love Your Feedback</h2>
          <p class="lead mb-5 text-muted">
            Share your thoughts about our coffee or let us know if you’d like to order something special.
          </p>
          <div class="row justify-content-center">
            <div class="col-lg-6">
              <div class="input-group input-group-lg mb-1">
              </div>
              <textarea class="form-control py-3 mb-3 rounded-3" rows="4" placeholder="Write your feedback or order details..."
                v-model="feedback"></textarea>
              <div v-if="errorMessage" class="alert alert-danger mb-3" role="alert">
                {{ errorMessage }}
              </div>
              <div v-if="successMessage" class="alert alert-success mb-3" role="alert">
                {{ successMessage }}
              </div>
              <button class="btn btn-dark px-5 py-3 rounded-3" type="button" @click="sendFeedback">
                Send Feedback
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <AppFooter />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Coffee, Flame, Heart, Check } from 'lucide-vue-next'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'
import { useAuthStore } from '@/stores/auth'
import { Modal } from 'bootstrap'
import { submitFeedback } from '@/api/users'

const authStore = useAuthStore()
const feedback = ref('')
const errorMessage = ref('')
const successMessage = ref('')

const sendFeedback = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  if (!authStore.isAuthenticated) {
    const modal = new Modal(document.getElementById('authModal')!)
    modal.show()
    return
  }
  if (feedback.value.trim() === '') {
    errorMessage.value = 'You cannot send empty feedback'
    return
  }
  try {
    await submitFeedback(feedback.value)
    successMessage.value = 'Feedback submitted successfully!'
    feedback.value = ''
  } catch (error: unknown) {
   const err = error as { response?: { data?: { message?: string } } }
   errorMessage.value = err.response?.data?.message || 'Failed to submit feedback'
 }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;500;600;700&display=swap');

.coffee-homepage {

  
  font-family: 'Inter', sans-serif;
  background-color: #faf8f5;
  
}

/* Navigation */


/* Hero Section */
.hero-section {
  min-height: 100vh;
  padding-top: 100px;
  background: #EEEAE4;
}

.hero-title {
  font-family: 'Playfair Display', serif;
  font-size: 5rem;
  line-height: 1.1;
  color: #2c2c2c;
  letter-spacing: -0.02em;
}

.hero-subtitle {
  font-family: 'Playfair Display', serif;
  font-size: 3.5 rem;
  line-height: 1.1;
  color: #2c2c2c;
  letter-spacing: -0.02em;
}

.hero-content .d-flex.gap-3.flex-wrap {
  margin-bottom: 5px;
}

.tracking-wide {
  letter-spacing: 0.1em;
  font-size: 0.875rem;
  font-weight: 600;
}

.hero-image-container {
  animation: float 6s ease-in-out infinite;
}

@keyframes float {

  0%,
  100% {
    transform: translateY(0px);
  }

  50% {
    transform: translateY(-20px);
  }
}

/* Features Section */
.features-section {
  background-color: #ffffff;
}

.feature-card {
  background-color: #faf8f5;
  border-radius: 1rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.feature-icon {
  color: #8C6353;
}

/* Signature Section */
.signature-section {
  background-color: #faf8f5;
}

/* CTA Section */
.cta-section {
  background-color: #ffffff;
}

.cta-card {
  background: #8C6353;
  color: white;
}

.cta-card h2,
.cta-card p {
  color: #EEEAE4  ;
}

.cta-card .text-muted {
  color: #EEEAE4  !important;
  justify-content: center;
}

.cta-card .form-control {
  background-color: #EEEAE4;
  border: none;
  border-radius: 0.5rem 0 0 0.5rem;
}

.cta-card .btn-dark {
  background-color: #2c2c2c;
  border: none;
  border-radius: 0 0.5rem 0.5rem 0;
}

/* Responsive */
@media (max-width: 992px) {
  .hero-title {
    font-size: 3.5rem;
  }
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2.5rem;
  }

  .hero-section {
    padding-top: 80px;
  }

  .hero-image-container {
    margin-top: 2rem;
    text-align: center;
  }

  .hero-image-container img {
    max-width: 100%;
    height: auto;
  }
}
</style>

