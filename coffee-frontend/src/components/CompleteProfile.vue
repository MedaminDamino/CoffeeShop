<template>
  <div class="main-container">
    <!-- Animated coffee beans background -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="coffee-bean bean-1"></div>
      <div class="coffee-bean bean-2"></div>
      <div class="coffee-bean bean-3"></div>
      <div class="coffee-bean bean-4"></div>
    </div>

    <div class="relative w-full max-w-md">
      <!-- Card with glass morphism effect -->
      <div class="card-container">
        <div class="backdrop-blur-xl rounded-3xl p-10 shadow-2xl border border-white/10" 
             style="background: rgba(231, 215, 201, 0.95)">
          
          <!-- Header section -->
          <div class="text-center mb-10">
            <div class="inline-block mb-4 px-6 py-2 rounded-full" 
                 style="background: linear-gradient(135deg, rgba(140, 99, 83, 0.15), rgba(231, 215, 201, 0.25))">
              <span class="text-sm font-semibold tracking-wide" style="color: #8C6353">☕ PROFILE SETUP</span>
            </div>
            <h2 class="text-3xl font-bold mb-3" style="color: #1A2845">
              Welcome to Rachfa Coffee
            </h2>
            <p class="text-base" style="color: #8C6353; opacity: 0.8">
              Complete your profile to unlock exclusive rewards
            </p>
          </div>

          <form @submit.prevent="saveProfile" class="space-y-6">
            <!-- Birthday Input -->
            <div class="form-group">
              <label class="block mb-2 font-semibold text-sm tracking-wide" style="color: #1A2845">
                🎂 Birthday
              </label>
              <div class="input-wrapper">
                <input
                  v-model="birthday"
                  type="date"
                  class="form-control-modern"
                  required
                />
              </div>
            </div>

            <!-- Password Input -->
            <div class="form-group">
              <label class="block mb-2 font-semibold text-sm tracking-wide" style="color: #1A2845">
                🔒 Password
              </label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  class="form-control-modern"
                  placeholder="Create a secure password"
                  required
                />
                <button
                  type="button"
                  class="password-toggle"
                  @click="showPassword = !showPassword"
                >
                  <svg v-if="showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              class="submit-button"
              style="background: linear-gradient(135deg, #1A2845 0%, #2a3f5f 100%); color: #E7D7C9"
            >
              <span class="relative z-10">Save & Continue</span>
              <div class="button-shine"></div>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import axios from 'axios'

const authStore = useAuthStore()
const router = useRouter()
const showPassword = ref(false)

const birthday = ref('')
const password = ref('')

onMounted(() => {
  // Check if user is authenticated and profile is incomplete
  if (!authStore.isAuthenticated) {
    router.push('/')
    return
  }

  if (authStore.user?.is_completed) {
    router.push('/')
    return
  }
})

const saveProfile = async () => {
  try {
    console.log('Frontend: Saving profile with birthday:', birthday.value, 'password length:', password.value.length)
    const response = await axios.post(
      'http://localhost:8000/api/complete-profile',
      { birthday: birthday.value, password: password.value, password_confirmation: password.value },
      { headers: { Authorization: `Bearer ${authStore.token}` } }
    )
    console.log('Frontend: Profile saved successfully:', response.data)

    // Refresh user info
    await authStore.fetchUser()

    router.push('/')
  } catch (error) {
    console.error('Frontend: Error saving profile:', error)
    if (axios.isAxiosError(error)) {
      console.error('Frontend: Axios error response:', error.response?.data)
      alert(error.response?.data?.message || 'Error saving profile')
    } else {
      alert('An unexpected error occurred')
    }
  }
}
</script>

<style scoped>

.main-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #E7D7C9 0%, #F0E3D8 100%);
  justify-content: center;
  padding: 1rem;
  width: 100vw;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  overflow-y: auto;
}


/* Card entrance animation */
.card-container {
    margin-top: 90px;

  animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Input wrapper styling */
.input-wrapper {
  position: relative;
  width: 25%; /* exactly 25% of the parent container */
  margin: 0 auto; 
}

/* Icon placed perfectly inside the input */
.input-icon {
  position: absolute;
  left: 0.75rem; /* fine-tuned distance from left edge */
  top: 50%;
  transform: translateY(-50%);
  color: rgba(140, 99, 83, 0.6);
  font-size: 1rem;
  pointer-events: none;
}

/* Input styling */
.form-control-modern {
  width: 100%; /* fill the 25% wrapper */
  padding: 0.875rem 1rem 0.875rem 2.2rem; /* enough space for icon */
  border: 2px solid rgba(140, 99, 83, 0.3);
  border-radius: 12px;
  background-color: #EEEAE4;
  color: #1A2845;
  font-size: 1rem;
  transition: all 0.3s ease;
  box-sizing: border-box; /* ensures true 25% width including borders/padding */
}

.form-control-modern:focus {
  outline: none;
  border-color: #8C6353;
  background-color: #fff;
  box-shadow: 0 0 0 4px rgba(140, 99, 83, 0.1);
}

.form-control-modern::placeholder {
  color: rgba(140, 99, 83, 0.5);
}

.password-toggle {
  position: absolute;
  right: 1rem;
  top: 0.5rem;
  background: none;
  border: none;
  color: #8C6353;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  z-index: 2;
}

.password-toggle:hover {
  color: #1A2845;
  transform: scale(1.1);
}

/* Date input specific styling */
input[type="date"].form-control-modern {
  padding: 0.875rem 1rem;
}

input[type="date"]::-webkit-calendar-picker-indicator {
  cursor: pointer;
  filter: opacity(0.6);
}

input[type="date"]::-webkit-calendar-picker-indicator:hover {
  filter: opacity(1);
}

/* Form group animations */
.form-group {
  animation: fadeIn 0.5s ease-out forwards;
  opacity: 0;
  text-align: center;
}

.form-group:nth-child(1) {
  animation-delay: 0.2s;
}

.form-group:nth-child(2) {
  animation-delay: 0.35s;
}

@keyframes fadeIn {
  to {
    opacity: 1;
  }
}

/* Submit button styling */
.submit-button {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 1rem auto 0;
  width: 12%;
  height: 60px;
  padding: 0.6rem 1rem;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  transform: scale(1);
}

.submit-button:hover {
  transform: scale(1.02);
  box-shadow: 0 12px 28px rgba(26, 40, 69, 0.4);
}

.submit-button:active {
  transform: scale(0.98);
}

.button-shine {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  transform: translateX(-100%);
  transition: transform 0.7s;
}

.submit-button:hover .button-shine {
  transform: translateX(100%);
}

.submit-button::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 8px;
  padding: 2px;
  background: linear-gradient(45deg, transparent, rgba(231, 215, 201, 0.4), transparent);
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.3s;
}

.submit-button:hover::before {
  opacity: 1;
}

/* Floating coffee beans */
.coffee-bean {
  position: absolute;
  width: 50px;
  height: 50px;
  background: radial-gradient(circle at 30% 30%, rgba(167, 124, 92, 0.15), rgba(140, 99, 83, 0.1));
  border-radius: 50% 50% 50% 0;
  animation: float-beans 25s ease-in-out infinite;
  filter: blur(1px);
}

.bean-1 {
  top: 10%;
  left: 5%;
  animation-delay: 0s;
  transform: rotate(45deg);
}

.bean-2 {
  top: 50%;
  right: 8%;
  animation-delay: 8s;
  transform: rotate(-30deg);
}

.bean-3 {
  bottom: 15%;
  left: 10%;
  animation-delay: 16s;
  transform: rotate(20deg);
}

.bean-4 {
  top: 30%;
  right: 20%;
  animation-delay: 12s;
  transform: rotate(60deg);
  opacity: 0.5;
}

@keyframes float-beans {
  0%, 100% {
    transform: translate(0, 0) rotate(0deg);
    opacity: 0.2;
  }
  25% {
    transform: translate(20px, -20px) rotate(90deg);
    opacity: 0.4;
  }
  50% {
    transform: translate(40px, 0) rotate(180deg);
    opacity: 0.2;
  }
  75% {
    transform: translate(20px, 20px) rotate(270deg);
    opacity: 0.4;
  }
}

/* Mobile optimizations */
@media (max-width: 576px) {
  .backdrop-blur-xl {
    padding: 2rem 1.5rem;
  }
  
  .submit-button {
    width: 100%;
  }
}
</style>