<template>
  <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="authModalLabel">{{ isLogin ? 'Login' : 'Sign Up' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Tab Navigation -->
          <ul class="nav nav-tabs mb-3" id="authTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button
                class="nav-link"
                :class="{ active: isLogin }"
                id="login-tab"
                type="button"
                @click="switchToLogin"
                role="tab"
              >
                Login
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button
                class="nav-link"
                :class="{ active: !isLogin }"
                id="signup-tab"
                type="button"
                @click="switchToSignup"
                role="tab"
              >
                Sign Up
              </button>
            </li>
          </ul>

          <!-- Login Form -->
          <Form v-if="isLogin" @submit="handleLogin" :validation-schema="loginSchema" class="auth-form">
            <div class="mb-3">
              <label for="loginUsername" class="form-label">Username or Email</label>
              <Field
                id="loginUsername"
                name="username"
                type="text"
                class="form-control"
                placeholder="Enter your username or email"
              />
              <ErrorMessage name="username" class="invalid-feedback d-block" />
            </div>
            <div class="mb-3">
              <label for="loginPassword" class="form-label">Password</label>
              <div class="input-group">
                <Field
                  id="loginPassword"
                  name="password"
                  :type="showLoginPassword ? 'text' : 'password'"
                  class="form-control"
                  placeholder="Enter your password"
                />
                <button
                  type="button"
                  class="btn btn-outline-secondary"
                  @click="showLoginPassword = !showLoginPassword"
                >
                  <i :class="showLoginPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
              <ErrorMessage name="password" class="invalid-feedback d-block" />
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary" :disabled="authStore.isLoading">
                <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                Login
              </button>
            </div>
            <div class="text-center mt-3">
              <button type="button" class="btn btn-link p-0" @click="showForgotPassword">Forgot Password?</button>
            </div>
          </Form>

          <!-- Signup Form -->
          <Form v-else @submit="handleSignup" :validation-schema="signupSchema" class="auth-form" v-slot="{ values }">
            <div class="mb-3">
              <label for="signupUsername" class="form-label">Username</label>
              <Field
                id="signupUsername"
                name="username"
                type="text"
                class="form-control"
                placeholder="Choose a username"
              />
              <ErrorMessage name="username" class="invalid-feedback d-block" />
            </div>
            <div class="mb-3">
              <label for="signupEmail" class="form-label">Email address</label>
              <div class="input-group">
                <Field
                  id="signupEmail"
                  name="email"
                  type="email"
                  class="form-control"
                  placeholder="Enter your email"
                  @blur="handleEmailBlur(values.email as string)"
                />
                <button
                  type="button"
                  class="btn btn-outline-primary"
                  @click="sendVerificationCode(values.email as string)"
                  :disabled="!values.email || sendingCode || !isValidEmail(values.email as string)"
                >
                  <span v-if="sendingCode" class="spinner-border spinner-border-sm me-1" role="status"></span>
                  {{ codeSent ? 'Resend Code' : 'Send Code' }}
                </button>
              </div>
              <ErrorMessage name="email" class="invalid-feedback d-block" />
              <small v-if="codeSent" class="text-success d-block mt-1">
                <i class="bi bi-check-circle-fill me-1"></i>Verification code sent!
              </small>
            </div>
            <div class="mb-3" v-if="codeSent">
              <label for="verificationCode" class="form-label">Verification Code</label>
              <Field
                id="verificationCode"
                name="verification_code"
                type="text"
                class="form-control"
                placeholder="Enter 6-digit code"
                maxlength="6"
              />
              <ErrorMessage name="verification_code" class="invalid-feedback d-block" />
            </div>
            <div class="mb-3">
              <label for="signupBirthday" class="form-label">Birthday</label>
              <Field
                id="signupBirthday"
                name="birthday"
                type="date"
                class="form-control"
              />
              <ErrorMessage name="birthday" class="invalid-feedback d-block" />
            </div>
            <div class="mb-3">
              <label for="signupPassword" class="form-label">Password</label>
              <div class="input-group">
                <Field
                  id="signupPassword"
                  name="password"
                  :type="showSignupPassword ? 'text' : 'password'"
                  class="form-control"
                  placeholder="Create a password"
                />
                <button
                  type="button"
                  class="btn btn-outline-secondary"
                  @click="showSignupPassword = !showSignupPassword"
                >
                  <i :class="showSignupPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
              <ErrorMessage name="password" class="invalid-feedback d-block" />
            </div>
            <div class="mb-3">
              <label for="signupConfirmPassword" class="form-label">Confirm Password</label>
              <div class="input-group">
                <Field
                  id="signupConfirmPassword"
                  name="password_confirmation"
                  :type="showSignupConfirmPassword ? 'text' : 'password'"
                  class="form-control"
                  placeholder="Confirm your password"
                />
                <button
                  type="button"
                  class="btn btn-outline-secondary"
                  @click="showSignupConfirmPassword = !showSignupConfirmPassword"
                >
                  <i :class="showSignupConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
              <ErrorMessage name="password_confirmation" class="invalid-feedback d-block" />
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary" :disabled="authStore.isLoading || !codeSent">
                <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                Sign Up
              </button>
            </div>
            <small v-if="!codeSent" class="text-muted d-block text-center mt-2">
              Please verify your email before signing up
            </small>
          </Form>

          <!-- Error Alert -->
          <div v-if="authStore.error" class="alert alert-danger mt-3" role="alert">
            {{ authStore.error }}
          </div>
          
          <!-- Verification Error -->
          <div v-if="verificationError" class="alert alert-danger mt-3" role="alert">
            {{ verificationError }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Forgot Password Modal -->
  <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <Form @submit="handleForgotPassword" :validation-schema="forgotPasswordSchema" class="auth-form">
            <div class="mb-3">
              <label for="forgotEmail" class="form-label">Email address</label>
              <Field
                id="forgotEmail"
                name="email"
                type="email"
                class="form-control"
                placeholder="Enter your email"
              />
              <ErrorMessage name="email" class="invalid-feedback d-block" />
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary" :disabled="authStore.isLoading">
                <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                Send Reset Email
              </button>
            </div>
          </Form>
          <div v-if="authStore.error" class="alert alert-danger mt-3" role="alert">
            {{ authStore.error }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Form, Field, ErrorMessage } from 'vee-validate'
import { object, string, date, ref as yupRef } from 'yup'
import { useAuthStore } from '@/stores/auth'
import { Modal } from 'bootstrap'
import { sendVerificationCodeAPI, verifyEmailCodeAPI } from '@/api/verification'  

const authStore = useAuthStore()
const isLogin = ref(true)
const showLoginPassword = ref(false)
const showSignupPassword = ref(false)
const showSignupConfirmPassword = ref(false)

// Verification state
const codeSent = ref(false)
const sendingCode = ref(false)
const verificationError = ref<string | null>(null)
const lastEmailSent = ref<string>('')

// Validation schemas
const loginSchema = object({
  username: string().required('Username or email is required'),
  password: string().required('Password is required')
})

const signupSchema = object({
  username: string().required('Username is required').min(3, 'Username must be at least 3 characters'),
  email: string().required('Email is required').email('Invalid email format'),
  verification_code: string().when('$codeSent', {
    is: true,
    then: (schema) => schema.required('Verification code is required').length(6, 'Code must be 6 digits'),
    otherwise: (schema) => schema.notRequired()
  }),
  birthday: date()
    .required('Birthday is required')
    .max(new Date(), 'Birthday cannot be in the future')
    ,
  password: string()
    .required('Password is required')
    .min(8, 'Password must be at least 8 characters')
    .matches(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/,
      'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character'),
  password_confirmation: string()
    .required('Please confirm your password')
    .oneOf([yupRef('password')], 'Passwords must match')
})

const forgotPasswordSchema = object({
  email: string().required('Email is required').email('Invalid email format')
})

// Define proper types for form data
interface LoginFormData {
  username: string
  password: string
}

interface SignupFormData {
  username: string
  email: string
  birthday: string
  password: string
  password_confirmation: string
  verification_code: string
}

interface ForgotPasswordFormData {
  email: string
}

// Email validation helper
const isValidEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

// Handle email blur to potentially send code
const handleEmailBlur = (email: string) => {
  if (email && isValidEmail(email) && email !== lastEmailSent.value && !codeSent.value) {
    // Optionally auto-send code on blur
    // sendVerificationCode(email)
  }
}

// Send verification code
const sendVerificationCode = async (email: string) => {
  if (!email || !isValidEmail(email)) {
    verificationError.value = 'Please enter a valid email address'
    return
  }

  sendingCode.value = true
  verificationError.value = null

  try {
    await sendVerificationCodeAPI(email)
    codeSent.value = true
    lastEmailSent.value = email
    verificationError.value = null
  } catch (error: unknown) {
    const err = error as { response?: { data?: { message?: string } } }
    verificationError.value = err.response?.data?.message || 'Failed to send verification code. Please try again.'
    codeSent.value = false
  } finally {
    sendingCode.value = false
  }
}

// Form handlers with proper typing for VeeValidate compatibility
const handleLogin = async (values: Record<string, unknown>) => {
  const loginData = values as unknown as LoginFormData
  try {
    await authStore.loginUser(loginData)
    // Close modal on success
    const modal = Modal.getInstance(document.getElementById('authModal')!)
    modal?.hide()

    // Role-based redirect
    const role = authStore.user?.role
    if (role === 'admin' || role === 'super_admin') {
      // Import router here to avoid circular dependency
      const router = (await import('vue-router')).useRouter()
      router.push('/admin')
    }
  } catch (error: unknown) {
    console.error('Login failed:', error)
    // Error is handled by the store
  }
}

const handleSignup = async (values: Record<string, unknown>) => {
  const signupData = values as unknown as SignupFormData
  
  // Verify the code first
  try {
    verificationError.value = null
    
    // Verify code before signup
    const verifyResponse = await verifyEmailCodeAPI(signupData.email, signupData.verification_code)
    
    if (!verifyResponse.valid) {
      verificationError.value = 'Invalid verification code. Please try again.'
      return
    }

    // Proceed with signup
    await authStore.registerUser(signupData)
    
    // Close modal on success
    const modal = Modal.getInstance(document.getElementById('authModal')!)
    modal?.hide()
    
    // Reset verification state
    codeSent.value = false
    lastEmailSent.value = ''
  } catch (error: unknown) {
    console.error('Signup failed:', error)
    const err = error as { response?: { data?: { message?: string } } }
    verificationError.value = err.response?.data?.message || 'Signup failed. Please try again.'
  }
}

const handleForgotPassword = async (values: Record<string, unknown>) => {
  const forgotData = values as unknown as ForgotPasswordFormData
  try {
    await authStore.sendForgotPassword(forgotData.email)
    // Close modal on success
    const modal = Modal.getInstance(document.getElementById('forgotPasswordModal')!)
    modal?.hide()
    // Show success message
    alert('Password reset email sent successfully!')
  } catch (error: unknown) {
    console.error('Forgot password failed:', error)
    // Error is handled by the store
  }
}

// Tab switching
const switchToLogin = () => {
  isLogin.value = true
  codeSent.value = false
  verificationError.value = null
}

const switchToSignup = () => {
  isLogin.value = false
  verificationError.value = null
}

const showForgotPassword = () => {
  const authModal = Modal.getInstance(document.getElementById('authModal')!)
  authModal?.hide()
  const forgotModal = new Modal(document.getElementById('forgotPasswordModal')!)
  forgotModal.show()
}
</script>

<style scoped>
.auth-form {
  max-width: 100%;
}

.invalid-feedback {
  color: #dc3545;
  font-size: 0.875em;
  margin-top: 0.25rem;
}

.text-success {
  color: #198754;
}
</style>