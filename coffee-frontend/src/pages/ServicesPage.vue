<template>
  <div class="services-page">
    <div class="hero-section bg-success text-white py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-4">Our Services</h1>
            <p class="lead mb-0">
              From premium coffee to catering services, we provide exceptional experiences
              for every occasion.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="container py-5">
      <div class="row">
        <div class="col-lg-4 mb-4">
          <div class="card h-100 border-0 shadow-lg">
            <div class="card-body text-center p-4">
              <i class="bi bi-cup-hot text-success fs-1 mb-3"></i>
              <h4 class="card-title">Premium Coffee</h4>
              <p class="card-text">
                Expertly crafted espresso-based drinks, pour-overs, and specialty beverages
                made with ethically sourced beans from around the world.
              </p>
              <ul class="list-unstyled small text-muted">
                <li>• Single-origin coffees</li>
                <li>• Custom espresso drinks</li>
                <li>• Iced and hot specialty drinks</li>
                <li>• Coffee tasting flights</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="col-lg-4 mb-4">
          <div class="card h-100 border-0 shadow-lg">
            <div class="card-body text-center p-4">
              <i class="bi bi-calendar-check text-success fs-1 mb-3"></i>
              <h4 class="card-title">Table Reservations</h4>
              <p class="card-text">
                Reserve your table for special occasions, business meetings, or casual gatherings.
                Our comfortable seating accommodates groups of all sizes.
              </p>
              <ul class="list-unstyled small text-muted">
                <li>• Indoor and outdoor seating</li>
                <li>• Private event spaces</li>
                <li>• Catering coordination</li>
                <li>• Custom menu planning</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="col-lg-4 mb-4">
          <div class="card h-100 border-0 shadow-lg">
            <div class="card-body text-center p-4">
              <i class="bi bi-truck text-success fs-1 mb-3"></i>
              <h4 class="card-title">Catering Services</h4>
              <p class="card-text">
                Bring the Coffee Shop experience to your event with our full-service catering.
                Perfect for weddings, corporate events, and celebrations.
              </p>
              <ul class="list-unstyled small text-muted">
                <li>• Full coffee service</li>
                <li>• Breakfast and lunch menus</li>
                <li>• Custom beverage stations</li>
                <li>• On-site barista service</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-5">
        <div class="col-lg-6 mb-4">
          <div class="card border-0 shadow">
            <div class="card-body p-4">
              <h4 class="card-title mb-3">Coffee Education</h4>
              <p class="card-text">
                Join our coffee education programs to learn about the art and science of coffee.
                From bean to cup, discover what makes great coffee great.
              </p>
              <div class="row">
                <div class="col-sm-6">
                  <h6>Workshops</h6>
                  <ul class="list-unstyled small">
                    <li>• Barista training</li>
                    <li>• Coffee cupping</li>
                    <li>• Home brewing techniques</li>
                  </ul>
                </div>
                <div class="col-sm-6">
                  <h6>Tastings</h6>
                  <ul class="list-unstyled small">
                    <li>• Single-origin flights</li>
                    <li>• Seasonal specials</li>
                    <li>• Guest roaster features</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6 mb-4">
          <div class="card border-0 shadow">
            <div class="card-body p-4">
              <h4 class="card-title mb-3">Loyalty Program</h4>
              <p class="card-text">
                Join our rewards program and earn points with every purchase. Redeem for free drinks,
                merchandise, and exclusive experiences.
              </p>
              <div class="row">
                <div class="col-sm-6">
                  <h6>Earn Points</h6>
                  <ul class="list-unstyled small">
                    <li>• 1 point per $1 spent</li>
                    <li>• Bonus points for referrals</li>
                    <li>• Special event multipliers</li>
                  </ul>
                </div>
                <div class="col-sm-6">
                  <h6>Redeem Rewards</h6>
                  <ul class="list-unstyled small">
                    <li>• Free coffee drinks</li>
                    <li>• Merchandise discounts</li>
                    <li>• Exclusive event access</li>
                  </ul>
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
// Services page component
</script>

<style scoped>
.hero-section {
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.services-page {
  min-height: 70vh;
}

.card {
  transition: transform 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
}
</style>