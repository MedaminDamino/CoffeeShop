<template>
  <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
      <div class="modal-content modern-auth-modal">
        <!-- Custom Header with Tabs -->
        <div class="auth-header">
          <div class="auth-tabs">
            <button
              :class="['auth-tab', { active: isLogin }]"
              @click="switchToLogin"
              type="button"
            >
              Login
            </button>
            <button
              :class="['auth-tab', { active: !isLogin }]"
              @click="switchToSignup"
              type="button"
            >
              Sign Up
            </button>
          </div>
          <div class="tab-indicator" :style="{ left: isLogin ? '0%' : '50%' }"></div>
          <button type="button" class="custom-close" data-bs-dismiss="modal" aria-label="Close">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <div class="modal-body auth-body">
          <!-- Login Form -->
          <Form v-if="isLogin" @submit="handleLogin" :validation-schema="loginSchema" class="auth-form">
            <!-- Username/Email -->
            <div class="form-group">
              <label class="form-label-modern">Username or Email</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <Field
                  name="username"
                  type="text"
                  class="form-control-modern"
                  placeholder="Enter your username or email"
                />
              </div>
              <ErrorMessage name="username" class="error-message" />
            </div>

            <!-- Password -->
            <div class="form-group">
              <label class="form-label-modern">Password</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <Field
                  name="password"
                  :type="showLoginPassword ? 'text' : 'password'"
                  class="form-control-modern"
                  placeholder="Enter your password"
                />
                <button
                  type="button"
                  class="password-toggle"
                  @click="showLoginPassword = !showLoginPassword"
                >
                  <svg v-if="!showLoginPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                  </svg>
                </button>
              </div>
              <ErrorMessage name="password" class="error-message" />
            </div>

            <!-- Forgot Password -->
            <div class="text-end mb-4">
              <button type="button" class="forgot-password-link" @click="showForgotPassword">
                Forgot Password?
              </button>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit" :disabled="authStore.isLoading">
              <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2"></span>
              {{ authStore.isLoading ? 'Logging in...' : 'Login' }}
            </button>
          </Form>

          <!-- Signup Form -->
          <Form v-else @submit="handleSignup" :validation-schema="signupSchema" class="auth-form" v-slot="{ values }">
            <!-- Username -->
            <div class="form-group">
              <label class="form-label-modern">Username</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <Field
                  name="username"
                  type="text"
                  class="form-control-modern"
                  placeholder="Choose a username"
                />
              </div>
              <ErrorMessage name="username" class="error-message" />
            </div>

            <!-- Email with Send Code -->
            <div class="form-group">
              <label class="form-label-modern">Email Address</label>
              <div class="email-verification-wrapper">
                <div class="input-wrapper flex-grow-1">
                  <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                  </svg>
                  <Field
                    name="email"
                    type="email"
                    class="form-control-modern"
                    placeholder="Enter your email"
                    @blur="handleEmailBlur(values.email as string)"
                  />
                </div>
                <button
                  type="button"
                  class="btn-send-code"
                  @click="sendVerificationCode(values.email as string)"
                  :disabled="!values.email || sendingCode || !isValidEmail(values.email as string)"
                >
                  <span v-if="sendingCode" class="spinner-border spinner-border-sm"></span>
                  <svg v-else-if="codeSent" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                  </svg>
                </button>
              </div>
              <ErrorMessage name="email" class="error-message" />
              <div v-if="codeSent" class="verification-success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Verification code sent!
              </div>
            </div>

            <!-- Verification Code -->
            <div v-if="codeSent" class="form-group verification-code-group">
              <label class="form-label-modern">Verification Code</label>
              <Field
                name="verification_code"
                type="text"
                class="form-control-modern text-center verification-input"
                placeholder="000000"
                maxlength="6"
              />
              <ErrorMessage name="verification_code" class="error-message" />
            </div>

            <!-- Birthday -->
            <div class="form-group">
              <label class="form-label-modern">Birthday</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="16" y1="2" x2="16" y2="6"></line>
                  <line x1="8" y1="2" x2="8" y2="6"></line>
                  <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <Field
                  name="birthday"
                  type="date"
                  class="form-control-modern"
                />
              </div>
              <ErrorMessage name="birthday" class="error-message" />
            </div>

            <!-- Password -->
            <div class="form-group">
              <label class="form-label-modern">Password</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <Field
                  name="password"
                  :type="showSignupPassword ? 'text' : 'password'"
                  class="form-control-modern"
                  placeholder="Create a password"
                />
                <button
                  type="button"
                  class="password-toggle"
                  @click="showSignupPassword = !showSignupPassword"
                >
                  <svg v-if="!showSignupPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                  </svg>
                </button>
              </div>
              <ErrorMessage name="password" class="error-message" />
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
              <label class="form-label-modern">Confirm Password</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <Field
                  name="password_confirmation"
                  :type="showSignupConfirmPassword ? 'text' : 'password'"
                  class="form-control-modern"
                  placeholder="Confirm your password"
                />
                <button
                  type="button"
                  class="password-toggle"
                  @click="showSignupConfirmPassword = !showSignupConfirmPassword"
                >
                  <svg v-if="!showSignupConfirmPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                  </svg>
                </button>
              </div>
              <ErrorMessage name="password_confirmation" class="error-message" />
            </div>

            <!-- Verification Notice -->
            <div v-if="!codeSent" class="verification-notice">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
              </svg>
              Please verify your email before signing up
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit" :disabled="authStore.isLoading || !codeSent">
              <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2"></span>
              {{ authStore.isLoading ? 'Creating Account...' : 'Sign Up' }}
            </button>
          </Form>

          <!-- Error Alerts -->
          <div v-if="authStore.error" class="alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="15" y1="9" x2="9" y2="15"></line>
              <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            {{ authStore.error }}
          </div>

          <div v-if="verificationError" class="alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="15" y1="9" x2="9" y2="15"></line>
              <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            {{ verificationError }}
          </div>

          <!-- Footer -->
          <div class="auth-footer">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            Secure authentication • Your data is protected
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Forgot Password Modal -->
  <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
      <div class="modal-content modern-auth-modal">
        <div class="auth-header single-title">
          <h5 class="modal-title">Forgot Password</h5>
          <button type="button" class="custom-close" data-bs-dismiss="modal" aria-label="Close">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        <div class="modal-body auth-body">
          <Form @submit="handleForgotPassword" :validation-schema="forgotPasswordSchema" class="auth-form">
            <div class="form-group">
              <label class="form-label-modern">Email Address</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <Field
                  name="email"
                  type="email"
                  class="form-control-modern"
                  placeholder="Enter your email"
                />
              </div>
              <ErrorMessage name="email" class="error-message" />
            </div>
            <button type="submit" class="btn-submit" :disabled="authStore.isLoading">
              <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2"></span>
              Send Reset Email
            </button>
          </Form>
          <div v-if="authStore.error" class="alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="15" y1="9" x2="9" y2="15"></line>
              <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
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
    .max(new Date(), 'Birthday cannot be in the future'),
  password: string()
    .required('Password is required')
    .min(8, 'Password must be at least 8 characters')
    .matches(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/,
      'Password must contain uppercase, lowercase, number, and special character'),
  password_confirmation: string()
    .required('Please confirm your password')
    .oneOf([yupRef('password')], 'Passwords must match')
})

const forgotPasswordSchema = object({
  email: string().required('Email is required').email('Invalid email format')
})

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

const isValidEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

const handleEmailBlur = (email: string) => {
  if (email && isValidEmail(email) && email !== lastEmailSent.value && !codeSent.value) {
    // Optional: auto-send code on blur
  }
}

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

const handleLogin = async (values: Record<string, unknown>) => {
  const loginData = values as unknown as LoginFormData
  try {
    await authStore.loginUser(loginData)
    const modal = Modal.getInstance(document.getElementById('authModal')!)
    modal?.hide()

    const role = authStore.user?.role
    if (role === 'admin' || role === 'super_admin') {
      const router = (await import('vue-router')).useRouter()
      router.push('/admin')
    }
  } catch (error: unknown) {
    console.error('Login failed:', error)
  }
}

const handleSignup = async (values: Record<string, unknown>) => {
  const signupData = values as unknown as SignupFormData
  
  try {
    verificationError.value = null
    
    const verifyResponse = await verifyEmailCodeAPI(signupData.email, signupData.verification_code)
    
    if (!verifyResponse.valid) {
      verificationError.value = 'Invalid verification code. Please try again.'
      return
    }

    await authStore.registerUser(signupData)
    
    const modal = Modal.getInstance(document.getElementById('authModal')!)
    modal?.hide()
    
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
    const modal = Modal.getInstance(document.getElementById('forgotPasswordModal')!)
    modal?.hide()
    alert('Password reset email sent successfully!')
  } catch (error: unknown) {
    console.error('Forgot password failed:', error)
  }
}

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
/* Color Palette: #1A2845 #E7D7C9 #8C6353 #EEEAE4 */

.modern-auth-modal {
  border-radius: 24px;
  border: none;
  overflow: hidden;
  background-color: #E7D7C9;
}

.auth-header {
  background-color: #1A2845;
  position: relative;
  padding: 0;
}

.auth-header.single-title {
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.auth-header .modal-title {
  color: #E7D7C9;
  font-weight: 600;
  font-size: 1.25rem;
  margin: 0;
}

.auth-tabs {
  display: flex;
  position: relative;
  z-index: 2;
}

.auth-tab {
  flex: 1;
  padding: 1.25rem 1rem;
  background: none;
  border: none;
  color: rgba(231, 215, 201, 0.5);
  font-weight: 600;
  font-size: 1.1rem;
  transition: all 0.3s ease;
  cursor: pointer;
}

.auth-tab.active {
  color: #E7D7C9;
}

.tab-indicator {
  position: absolute;
  bottom: 0;
  width: 50%;
  height: 3px;
  background-color: #8C6353;
  transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 3;
}

.custom-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(231, 215, 201, 0.1);
  border: none;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10;
  color: #E7D7C9;
}

.custom-close:hover {
  background: rgba(231, 215, 201, 0.2);
  transform: rotate(90deg);
}

.auth-body {
  padding: 2rem 1.5rem;
  background-color: #E7D7C9;
}

.auth-form {
  max-width: 100%;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label-modern {
  display: block;
  font-size: 0.9rem;
  font-weight: 600;
  color: #1A2845;
  margin-bottom: 0.5rem;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1rem;
  color: #8C6353;
  pointer-events: none;
  z-index: 2;
}

.form-control-modern {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 2px solid rgba(140, 99, 83, 0.3);
  border-radius: 12px;
  background-color: #EEEAE4;
  color: #1A2845;
  font-size: 1rem;
  transition: all 0.3s ease;
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

.email-verification-wrapper {
  display: flex;
  gap: 0.5rem;
  align-items: flex-start;
}

.btn-send-code {
  padding: 0.875rem 1rem;
  background-color: #1A2845;
  color: #E7D7C9;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 56px;
  flex-shrink: 0;
}

.btn-send-code:hover:not(:disabled) {
  background-color: #8C6353;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 40, 69, 0.2);
}

.btn-send-code:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.verification-success {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #8C6353;
  font-size: 0.875rem;
  margin-top: 0.5rem;
  animation: slideIn 0.3s ease;
}

.verification-code-group {
  animation: slideIn 0.4s ease;
}

.verification-input {
  letter-spacing: 0.5rem;
  font-size: 1.25rem;
  font-weight: 600;
  text-align: center;
}

.verification-notice {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background-color: rgba(140, 99, 83, 0.1);
  border-radius: 12px;
  color: #8C6353;
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.btn-submit {
  width: 100%;
  padding: 1rem;
  background-color: #1A2845;
  color: #E7D7C9;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 1.5rem;
}

.btn-submit:hover:not(:disabled) {
  background-color: #8C6353;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(26, 40, 69, 0.3);
}

.btn-submit:active:not(:disabled) {
  transform: translateY(0);
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.forgot-password-link {
  background: none;
  border: none;
  color: #8C6353;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
}

.forgot-password-link:hover {
  color: #1A2845;
  text-decoration: underline;
}

.error-message {
  color: #d9534f;
  font-size: 0.875rem;
  margin-top: 0.375rem;
  display: block;
  animation: slideIn 0.2s ease;
}

.alert-error {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background-color: rgba(217, 83, 79, 0.1);
  border: 1px solid rgba(217, 83, 79, 0.3);
  border-radius: 12px;
  color: #d9534f;
  font-size: 0.9rem;
  margin-top: 1rem;
  animation: slideIn 0.3s ease;
}

.auth-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(140, 99, 83, 0.2);
  color: #8C6353;
  font-size: 0.8rem;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Mobile Optimizations */
@media (max-width: 576px) {
  .auth-body {
    padding: 1.5rem 1.25rem;
  }

  .form-group {
    margin-bottom: 1.25rem;
  }

  .auth-tab {
    font-size: 1rem;
    padding: 1rem 0.75rem;
  }

  .form-control-modern {
    font-size: 16px; /* Prevents zoom on iOS */
  }

  .btn-submit {
    padding: 0.875rem;
    font-size: 1rem;
  }

  .email-verification-wrapper {
    flex-direction: column;
    gap: 0.75rem;
  }

  .btn-send-code {
    width: 100%;
    min-width: auto;
  }
}

/* Date input styling */
.form-control-modern[type="date"] {
  padding-right: 1rem;
}

.form-control-modern[type="date"]::-webkit-calendar-picker-indicator {
  cursor: pointer;
  filter: opacity(0.5);
  margin-left: 0.5rem;
}

.form-control-modern[type="date"]::-webkit-calendar-picker-indicator:hover {
  filter: opacity(1);
}

/* Spinner */
.spinner-border {
  width: 1rem;
  height: 1rem;
  border-width: 2px;
}

.spinner-border-sm {
  width: 0.875rem;
  height: 0.875rem;
  border-width: 2px;
}

/* Touch interactions */
@media (hover: none) {
  .btn-submit:active:not(:disabled),
  .btn-send-code:active:not(:disabled) {
    transform: scale(0.97);
  }
}

/* Smooth scroll for modal */
.modal-body {
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

/* Focus visible for accessibility */
*:focus-visible {
  outline: 2px solid #8C6353;
  outline-offset: 2px;
}

button:focus-visible {
  outline-offset: 4px;
}

/* Animation for modal entrance */
.modal.fade .modal-dialog {
  transition: transform 0.3s ease-out;
}

.modal.show .modal-dialog {
  transform: none;
}
</style>