<template>
  <div class="text-center mt-5">
    <h3>Signing you in with Google...</h3>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'
import { onMounted } from 'vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()



const closeMenu = () => {
  const navbarCollapse = document.getElementById('navbarNav')
  if (navbarCollapse?.classList.contains('show')) {
    navbarCollapse.classList.remove('show')
  }
}
const openLoginModal = () => {
  closeMenu()
  window.dispatchEvent(new CustomEvent('openAuthModal'))
}

onMounted(async () => {
  console.log('Frontend: GoogleCallback component mounted, processing OAuth response')
  const token = route.query.token as string // get ?token= from URL
  const userParam = route.query.user as string // get ?user= from URL
  console.log('Frontend: Received token:', !!token, 'userParam:', !!userParam)

   if (!token) {
    console.log('Frontend: No token received, opening login modal')
    // Instead of redirecting to /login, open your login modal
    openLoginModal()
    return
  }

  if (token) {
    console.log('Frontend: Setting token in auth store')
    // Save token in Pinia/localStorage
    authStore.setToken(token)

    if (userParam) {
      try {
        const user = JSON.parse(decodeURIComponent(userParam))
        console.log('Frontend: Parsed user data:', user)
        console.log('Frontend: User is_completed value:', user.is_completed, 'type:', typeof user.is_completed)
        authStore.user = user
        // Redirect based on user status - check for truthy/falsy values
        const isProfileIncomplete = !user.is_completed || user.is_completed === false || user.is_completed === null || user.is_completed === undefined || user.is_completed === 0 || user.is_completed === "0" || user.is_completed === ""
        console.log('Frontend: Is profile incomplete?', isProfileIncomplete)

        if (isProfileIncomplete) {
          console.log('Frontend: Incomplete profile, redirecting to complete-profile')
          router.push('/complete-profile')
        } else {
          console.log('Frontend: Profile complete, redirecting to home')
          router.push('/')
        }
      } catch (err) {
        console.error('Frontend: Error parsing user:', err)
        console.log('Frontend: Fetching user data from API')
        await authStore.fetchUser()
        router.push('/')
      }

    }
  }
})
</script>
