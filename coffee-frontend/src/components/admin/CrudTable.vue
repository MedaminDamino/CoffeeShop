<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'

interface Column<T = unknown> {
  key: string
  label: string
  formatter?: (value: T) => string       
}

type FieldType = 'text' | 'number' | 'textarea' | 'select' | 'checkbox' | 'datepicker' | 'datetime-local' | 'date' | 'time' | 'password'

interface SelectOption {
  label: string
  value: string | number | boolean
}

interface CreateField {
  key: string
  label: string
  type: FieldType
  required?: boolean
  min?: number
  max?: number
  step?: number
  options?: SelectOption[]
}

const props = defineProps<{
  title: string
  columns: Column[]
  fetchAll: () => Promise<unknown[]>
  createTitle?: string
  createFields: CreateField[]
  onCreate: (payload: Record<string, unknown>) => Promise<unknown>
  initialValues?: Record<string, unknown>
}>()

const rows = ref<unknown[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

const showModal = ref(false)
const submitting = ref(false)
const form = ref<Record<string, unknown>>({})
const errorMessage = ref<string | null>(null)
const showRetry = ref(false)

function resetForm() {
  const base: Record<string, unknown> = {}
  for (const field of props.createFields) {
    if (props.initialValues && field.key in props.initialValues) {
      base[field.key] = props.initialValues[field.key]
    } else {
      base[field.key] = field.type === 'checkbox' ? false : null
    }
  }
  form.value = base
}

async function reload() {
  loading.value = true
  error.value = null
  try {
    rows.value = await props.fetchAll()
  } catch {
    error.value = 'Failed to load data'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  resetForm()
  await reload()
})

watch(
  () => props.createFields,
  () => {
    resetForm()
  },
  { deep: true },
)

defineExpose({ reload })

function getCellValue(row: unknown, key: string) {
  const anyRow = row as Record<string, unknown>
  return anyRow
    ? (anyRow[key] as unknown as string | number | boolean | null | undefined)
    : undefined
}

function getFormattedCellValue(row: unknown, column: Column) {
  const value = getCellValue(row, column.key)
  return column.formatter ? column.formatter(value) : value
}

async function submit() {
  submitting.value = true
  errorMessage.value = null
  showRetry.value = false
  try {
    for (const f of props.createFields) {
      if (
        f.required &&
        (form.value[f.key] === null || form.value[f.key] === '' || form.value[f.key] === undefined)
      ) {
        throw new Error(`${f.label} is required`)
      }
    }
    await props.onCreate(form.value)
    showModal.value = false
    resetForm()
    await reload()
  } catch (e: unknown) {
    console.error(e)
    const error = e as { message?: string; response?: { status: number; data?: { error?: string } } }
    if (error.message) {
      errorMessage.value = error.message
    } else if (error.response) {
      if (error.response.status === 409) {
        errorMessage.value = error.response.data?.error ?? 'Conflict error'
      } else if (error.response.status === 422) {
        const data = error.response.data as { errors?: Record<string, string[]>, message?: string }
        const errors = data?.errors
        if (errors && typeof errors === 'object') {
          const messages = Object.values(errors).flat().join(', ')
          errorMessage.value = messages
        } else {
          errorMessage.value = data?.message ?? 'Validation failed. Please check your input.'
        }
      } else if (error.response.status >= 500) {
        errorMessage.value = 'We encountered a technical issue while processing your request. Our team has been notified. Please try again in a few moments.'
        showRetry.value = true
      } else {
        errorMessage.value = 'An error occurred. Please check your input.'
      }
    } else {
      errorMessage.value = 'Network error. Please check your connection.'
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="admin-container">
    <!-- Header -->
    <div class="admin-header">
      <div class="header-content">
        <div class="title-section">
          <h1 class="admin-title">{{ title }}</h1>
          <span class="record-count">{{ rows.length }} records</span>
        </div>
        <div class="header-actions">
          <slot name="header-extra"></slot>
          <button class="btn-add" @click="() => (showModal = true)">
            <i class="bi bi-plus-lg"></i>
            <span>Add New</span>
          </button>
          <div class="header-actions">
           <slot name="header-extra"></slot>
             <button class="btn-refresh" @click="reload()">
               <i class="bi bi-arrow-clockwise"></i>Refresh
             </button>
        </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="status-card loading">
      <div class="spinner"></div>
      <span>Loading data...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="status-card error">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <div>
        <p class="error-title">{{ error }}</p>
        <button class="btn-retry" @click="reload">
          <i class="bi bi-arrow-clockwise"></i>
          Try Again
        </button>
      </div>
    </div>

    <!-- Table -->
    <div v-else class="table-container">
      <div class="table-wrapper">
        <table class="modern-table">
          <thead>
            <tr>
              <th v-for="c in columns" :key="c.key">
                <div class="th-content">
                  {{ c.label }}
                </div>
              </th>
              <th class="actions-header">
                <div class="th-content">Actions</div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in rows" :key="(r as any).id" class="table-row">
              <td v-for="c in columns" :key="c.key" v-html="getFormattedCellValue(r, c)"></td>
              <td class="actions-cell">
                <slot name="actions" :row="r">
                  <div class="action-buttons">
                    <button class="btn-action edit" disabled title="Edit">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn-action delete" disabled title="Delete">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </slot>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <teleport to="body">
      <transition name="modal">
        <div v-if="showModal" class="modal-overlay" @click="showModal = false">
          <div class="modern-modal" @click.stop>
            <button class="modal-close" @click="showModal = false">
              <i class="bi bi-x-lg"></i>
            </button>
            
            <div class="modal-header">
              <div class="modal-icon">
                <i class="bi bi-plus-circle"></i>
              </div>
              <h2 class="modal-title">{{ createTitle ?? `Add ${title}` }}</h2>
            </div>

            <div class="modal-body">
              <form @submit.prevent="submit" class="modal-form">
                <div v-for="field in createFields" :key="field.key" class="form-group">
                  <label class="form-label">
                    {{ field.label }}
                    <span v-if="field.required" class="required-star">*</span>
                  </label>

                  <!-- Text Input -->
                  <template v-if="field.type === 'text'">
                    <input 
                      v-model="form[field.key]" 
                      class="form-input" 
                      autocomplete="off"
                      :placeholder="`Enter ${field.label.toLowerCase()}`"
                    />
                  </template>

                  <!-- Date Input -->
                  <template v-else-if="field.type === 'date'">
                    <input
                      type="date"
                      class="form-input"
                      v-model="form[field.key]"
                    />
                  </template>

                  <!-- Password Input -->
                  <template v-else-if="field.type === 'password'">
                    <input
                      type="password"
                      class="form-input"
                      v-model="form[field.key]"
                      autocomplete="new-password"
                      :placeholder="`Enter ${field.label.toLowerCase()}`"
                    />
                  </template>

                  <!-- Time Input -->
                  <template v-else-if="field.type === 'time'">
                    <input
                      type="time"
                      class="form-input"
                      v-model="form[field.key]"
                    />
                  </template>

                  <!-- DateTime Input -->
                  <template v-else-if="field.type === 'datetime-local'">
                    <input
                      type="datetime-local"
                      class="form-input"
                      v-model="form[field.key]"
                    />
                  </template>

                  <!-- Textarea -->
                  <template v-else-if="field.type === 'textarea'">
                    <textarea
                      v-model="form[field.key] as string"
                      class="form-input form-textarea"
                      rows="4"
                      :placeholder="`Enter ${field.label.toLowerCase()}`"
                    ></textarea>
                  </template>

                  <!-- Number Input -->
                  <template v-else-if="field.type === 'number'">
                    <input
                      v-model.number="form[field.key]"
                      type="number"
                      class="form-input"
                      :min="field.min"
                      :max="field.max"
                      :step="field.step ?? 1"
                      :placeholder="`Enter ${field.label.toLowerCase()}`"
                    />
                  </template>

                  <!-- Select -->
                  <template v-else-if="field.type === 'select'">
                    <select v-model="form[field.key]" class="form-select">
                      <option :value="null" disabled>Select {{ field.label.toLowerCase() }}</option>
                      <option
                        v-for="opt in field.options || []"
                        :key="String(opt.value)"
                        :value="opt.value"
                      >
                        {{ opt.label }}
                      </option>
                    </select>
                  </template>

                  <!-- Checkbox -->
                  <template v-else-if="field.type === 'checkbox'">
                    <div class="checkbox-wrapper">
                      <label class="checkbox-label">
                        <input
                          class="checkbox-input"
                          type="checkbox"
                          v-model="form[field.key] as boolean"
                        />
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">{{ field.label }}</span>
                      </label>
                    </div>
                  </template>
                </div>

                <!-- Error Message -->
                <transition name="fade">
                  <div v-if="errorMessage" class="error-message">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div class="error-content">
                      <p>{{ errorMessage }}</p>
                      <button 
                        v-if="showRetry" 
                        @click="submit" 
                        :disabled="submitting" 
                        class="btn-retry-inline"
                      >
                        <i class="bi bi-arrow-clockwise"></i>
                        Retry
                      </button>
                    </div>
                  </div>
                </transition>
              </form>
            </div>

            <div class="modal-footer">
              <button
                type="button"
                class="btn-modal-secondary"
                :disabled="submitting"
                @click="showModal = false"
              >
                Cancel
              </button>
              <button 
                type="button" 
                class="btn-modal-primary" 
                :disabled="submitting" 
                @click="submit"
              >
                <span v-if="submitting" class="spinner-small"></span>
                <i v-else class="bi bi-check-lg"></i>
                <span>{{ submitting ? 'Saving...' : 'Save' }}</span>
              </button>
            </div>
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>

<style scoped>
.admin-container {
  background: #FFFFFF;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(26, 40, 69, 0.08);
  margin: 30px auto; /* centers horizontally */
  width: fit-content; /* or set a fixed width */
}


/* Header */
.admin-header {
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.title-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.admin-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1A2845;
  margin: 0;
}

.record-count {
  display: inline-flex;
  align-items: center;
  padding: 0.375rem 0.875rem;
  background: #EEEAE4;
  color: #8C6353;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #1A2845;
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
}
.btn-refresh {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #1A2845;
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-add:hover {
  background: #8C6353;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 40, 69, 0.2);
}
.btn-refresh:hover {
  background: #8C6353;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 40, 69, 0.2);
}

.btn-add i {
  font-size: 1rem;
}
.btn-refresh i {
  font-size: 1rem;
}

/* Status Cards */
.status-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 2rem;
  border-radius: 16px;
  font-size: 1rem;
}

.status-card.loading {
  background: linear-gradient(135deg, #E7D7C9 0%, #EEEAE4 100%);
  color: #1A2845;
  justify-content: center;
}

.status-card.error {
  background: #fff5f5;
  color: #c53030;
  border: 2px solid #feb2b2;
}

.status-card i {
  font-size: 1.75rem;
}

.error-title {
  font-weight: 600;
  margin: 0 0 0.5rem 0;
}

.btn-retry {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: white;
  color: #c53030;
  border: 2px solid #feb2b2;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-retry:hover {
  background: #c53030;
  color: white;
}

.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid #EEEAE4;
  border-top-color: #1A2845;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Table */
.table-container {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  border: 2px solid #EEEAE4;
}

.table-wrapper {
  overflow-x: auto;
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
}

.modern-table thead {
  background: linear-gradient(135deg, #E7D7C9 0%, #EEEAE4 100%);
}

.modern-table th {
  padding: 1rem 1.25rem;
  text-align: left;
  font-weight: 700;
  color: #1A2845;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #E7D7C9;
}

.th-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.actions-header {
  text-align: right;
}

.actions-header .th-content {
  justify-content: flex-end;
}

.modern-table tbody tr {
  border-bottom: 1px solid #EEEAE4;
  transition: all 0.2s ease;
}

.modern-table tbody tr:hover {
  background: linear-gradient(135deg, rgba(231, 215, 201, 0.1) 0%, rgba(238, 234, 228, 0.1) 100%);
}

.modern-table td {
  padding: 1rem 1.25rem;
  color: #1A2845;
  font-size: 0.9375rem;
}

.actions-cell {
  text-align: right;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

.btn-action {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.btn-action.edit {
  background: #EEEAE4;
  color: #1A2845;
}

.btn-action.edit:hover:not(:disabled) {
  background: #8C6353;
  color: white;
  transform: scale(1.1);
}

.btn-action.delete {
  background: #fff5f5;
  color: #c53030;
}

.btn-action.delete:hover:not(:disabled) {
  background: #c53030;
  color: white;
  transform: scale(1.1);
}

.btn-action:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(26, 40, 69, 0.7);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
}

.modern-modal {
  background: white;
  border-radius: 24px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(26, 40, 69, 0.3);
  position: relative;
}

.modal-close {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  width: 40px;
  height: 40px;
  border: none;
  background: #EEEAE4;
  color: #1A2845;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  z-index: 1;
}

.modal-close:hover {
  background: #E7D7C9;
  transform: rotate(90deg);
}

.modal-header {
  padding: 2.5rem 2rem 1.5rem;
  background: linear-gradient(135deg, #E7D7C9 0%, #EEEAE4 100%);
  border-radius: 24px 24px 0 0;
  text-align: center;
}

.modal-icon {
  width: 72px;
  height: 72px;
  background: #1A2845;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  font-size: 1.75rem;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1A2845;
  margin: 0;
}

.modal-body {
  padding: 2rem;
  overflow-y: auto;
  flex: 1;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  font-weight: 600;
  color: #1A2845;
  margin-bottom: 0.625rem;
  font-size: 0.9375rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.required-star {
  color: #c53030;
  font-size: 1rem;
}

.form-input,
.form-select {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 2px solid #EEEAE4;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s ease;
  outline: none;
  background: white;
  color: #1A2845;
}

.form-input:focus,
.form-select:focus {
  border-color: #8C6353;
  box-shadow: 0 0 0 4px rgba(140, 99, 83, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
}

.form-select {
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%231A2845' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 16px;
  padding-right: 3rem;
}

.checkbox-wrapper {
  padding: 0.5rem 0;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  user-select: none;
}

.checkbox-input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.checkbox-custom {
  width: 24px;
  height: 24px;
  border: 2px solid #EEEAE4;
  border-radius: 6px;
  background: white;
  transition: all 0.3s ease;
  position: relative;
  flex-shrink: 0;
}

.checkbox-custom::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0);
  width: 10px;
  height: 10px;
  background: white;
  border-radius: 2px;
  transition: transform 0.2s ease;
}

.checkbox-input:checked + .checkbox-custom {
  background: #1A2845;
  border-color: #1A2845;
}

.checkbox-input:checked + .checkbox-custom::after {
  transform: translate(-50%, -50%) scale(1);
}

.checkbox-text {
  color: #1A2845;
  font-size: 0.9375rem;
}

.error-message {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem 1.25rem;
  background: #fff5f5;
  border: 2px solid #feb2b2;
  border-radius: 12px;
  color: #c53030;
}

.error-message i {
  font-size: 1.25rem;
  flex-shrink: 0;
  margin-top: 0.125rem;
}

.error-content {
  flex: 1;
}

.error-content p {
  margin: 0 0 0.5rem 0;
  font-size: 0.9375rem;
}

.btn-retry-inline {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: white;
  color: #c53030;
  border: 2px solid #feb2b2;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-retry-inline:hover:not(:disabled) {
  background: #c53030;
  color: white;
}

.btn-retry-inline:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.modal-footer {
  padding: 1.5rem 2rem;
  border-top: 2px solid #EEEAE4;
  display: flex;
  gap: 1rem;
  background: white;
  border-radius: 0 0 24px 24px;
}

.btn-modal-secondary,
.btn-modal-primary {
  flex: 1;
  padding: 0.875rem 1.5rem;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-modal-secondary {
  background: #EEEAE4;
  color: #1A2845;
}

.btn-modal-secondary:hover:not(:disabled) {
  background: #E7D7C9;
}

.btn-modal-primary {
  background: #1A2845;
  color: white;
}

.btn-modal-primary:hover:not(:disabled) {
  background: #8C6353;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 40, 69, 0.2);
}

.btn-modal-secondary:disabled,
.btn-modal-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

/* Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modern-modal,
.modal-leave-to .modern-modal {
  transform: scale(0.9) translateY(20px);
}

.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Responsive */
@media (max-width: 768px) {
  .admin-container {
    padding: 1.5rem;
    border-radius: 16px;
  }

  .admin-title {
    font-size: 1.5rem;
  }

  .header-content {
    flex-direction: column;
    align-items: stretch;
  }

  .title-section {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .header-actions {
    width: 100%;
  }

  .btn-add {
    width: 100%;
    justify-content: center;
  }
    .btn-refresh {
    width: 100%;
    justify-content: center;
  }

  .table-wrapper {
    overflow-x: scroll;
    -webkit-overflow-scrolling: touch;
  }

  .modern-table {
    min-width: 600px;
  }

  .modern-modal {
    max-height: 95vh;
    border-radius: 20px;
  }

  .modal-header {
    padding: 2rem 1.5rem 1.25rem;
  }

  .modal-body {
    padding: 1.5rem;
  }
}
</style>