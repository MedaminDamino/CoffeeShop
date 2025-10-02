<template>
  <div class="about-page">
    <div class="hero-section bg-primary text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-4">About Coffee Shop</h1>
            <p class="lead mb-0">
              Passionate about serving the finest coffee and creating memorable experiences
              for our community since 2010.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="container py-5">
      <div class="row">
        <div class="col-lg-6">
          <h2 class="mb-4">Our Story</h2>
          <p class="mb-4">
            Founded in 2010, Coffee Shop began as a small family-owned café with a simple mission:
            to serve exceptional coffee in a warm, welcoming environment. What started as a passion
            project has grown into a beloved community gathering place.
          </p>
          <p class="mb-4">
            We source our beans directly from sustainable farms around the world, ensuring that
            every cup tells a story of quality, ethics, and craftsmanship. Our expert baristas
            are dedicated to perfecting the art of coffee-making, from the traditional espresso
            to innovative specialty drinks.
          </p>
        </div>
        <div class="col-lg-6">
          <h2 class="mb-4">Our Values</h2>
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                  <i class="bi bi-heart-fill text-primary fs-1 mb-3"></i>
                  <h5 class="card-title">Quality</h5>
                  <p class="card-text small">
                    Only the finest beans and ingredients make it into our drinks.
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                  <i class="bi bi-people-fill text-primary fs-1 mb-3"></i>
                  <h5 class="card-title">Community</h5>
                  <p class="card-text small">
                    Building connections and creating memories in our café.
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                  <i class="bi bi-leaf-fill text-primary fs-1 mb-3"></i>
                  <h5 class="card-title">Sustainability</h5>
                  <p class="card-text small">
                    Committed to ethical sourcing and environmental responsibility.
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                  <i class="bi bi-star-fill text-primary fs-1 mb-3"></i>
                  <h5 class="card-title">Excellence</h5>
                  <p class="card-text small">
                    Striving for perfection in every cup we serve.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// About page component
</script>

<style scoped>
.hero-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.about-page {
  min-height: 70vh;
}
</style>