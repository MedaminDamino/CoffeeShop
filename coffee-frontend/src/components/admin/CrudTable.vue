<script setup lang="ts">
import { onMounted, ref, watch, computed } from 'vue'

interface Column<T = unknown> {
  key: string
  label: string
  formatter?: (value: T) => string
}

// Function to singularize table names
function singularize(word: string): string {
  if (word.endsWith('ies')) return word.slice(0, -3) + 'y'
  if (word.endsWith('es')) return word.slice(0, -2)
  if (word.endsWith('s')) return word.slice(0, -1)
  return word
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

interface PaginatedResponse<T = unknown> {
  data: T[]
  total: number
  current_page: number
  per_page: number
  last_page: number
}

type SortDirection = 'asc' | 'desc'

const props = defineProps<{
  title: string
  columns: Column[]
  fetchAll: (params?: { page?: number; per_page?: number; sort_by?: string; sort_direction?: SortDirection }) => Promise<unknown[] | PaginatedResponse>
  createTitle?: string
  createFields: CreateField[]
  onCreate: (payload: Record<string, unknown>) => Promise<unknown>
  initialValues?: Record<string, unknown>
  // Edit functionality
  editFields?: CreateField[]
  onUpdate?: (id: number | string, payload: Record<string, unknown>) => Promise<unknown>
  // Delete functionality
  onDelete?: (id: number | string) => Promise<unknown>
  // UI customization
  enableEdit?: boolean
  enableDelete?: boolean
  editTitle?: string
  deleteTitle?: string
  deleteMessage?: string
  // Pagination
  enablePagination?: boolean
  defaultPageSize?: number
}>()

const rows = ref<unknown[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

// Sorting state
const sortColumn = ref<string>('')
const sortDirection = ref<SortDirection>('asc')

// Pagination state
const currentPage = ref(1)
const pageSize = ref(props.defaultPageSize || 5)
const totalRecords = ref(0)
const totalPages = ref(0)

const showModal = ref(false)
const submitting = ref(false)
const form = ref<Record<string, unknown>>({})
const errorMessage = ref<string | null>(null)
const showRetry = ref(false)
const successMessage = ref<string | null>(null)

// Edit functionality
const showEditModal = ref(false)
const editingItem = ref<unknown>(null)
const editForm = ref<Record<string, unknown>>({})
const editSubmitting = ref(false)
const editErrorMessage = ref<string | null>(null)

// Delete functionality
const showDeleteModal = ref(false)
const deletingItem = ref<unknown>(null)
const deleteSubmitting = ref(false)
const deleteErrorMessage = ref<string | null>(null)


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
    const params = props.enablePagination
      ? { page: currentPage.value, per_page: pageSize.value, sort_by: sortColumn.value, sort_direction: sortDirection.value }
      : sortColumn.value ? { sort_by: sortColumn.value, sort_direction: sortDirection.value } : undefined
    const response = await props.fetchAll(params)

    if (props.enablePagination && response && typeof response === 'object' && 'data' in response) {
      const paginatedResponse = response as PaginatedResponse
      rows.value = paginatedResponse.data
      totalRecords.value = paginatedResponse.total
      totalPages.value = paginatedResponse.last_page
      currentPage.value = paginatedResponse.current_page
    } else {
      rows.value = response as unknown[]
      totalRecords.value = rows.value.length
      totalPages.value = 1
      currentPage.value = 1
    }
  } catch {
    error.value = 'Failed to load data'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  resetForm()
  // Load sort state from localStorage
  const savedSort = localStorage.getItem(`crud-table-sort-${props.title}`)
  if (savedSort) {
    try {
      const { column, direction } = JSON.parse(savedSort)
      sortColumn.value = column
      sortDirection.value = direction
    } catch {
      // Fallback to default sort
      if (props.columns.length > 0 && props.columns[0]) {
        sortColumn.value = props.columns[0].key
        sortDirection.value = 'asc'
      }
    }
  } else {
    // Set default sort to first column ascending
    if (props.columns.length > 0 && props.columns[0]) {
      sortColumn.value = props.columns[0].key
      sortDirection.value = 'asc'
    }
  }
  await reload()
})

watch(
  () => props.createFields,
  () => {
    resetForm()
  },
  { deep: true },
)

function changePage(page: number) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    reload()
  }
}

function changePageSize(size: number) {
  pageSize.value = size
  currentPage.value = 1
  reload()
}

function sortBy(column: string) {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = column
    sortDirection.value = 'asc'
  }
  // Save sort state to localStorage
  localStorage.setItem(`crud-table-sort-${props.title}`, JSON.stringify({
    column: sortColumn.value,
    direction: sortDirection.value
  }))
  currentPage.value = 1
  reload()
}

// Computed property for visible page numbers
const visiblePages = computed(() => {
  const pages: (number | string)[] = []
  const total = totalPages.value
  const current = currentPage.value

  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    pages.push(1)

    if (current > 4) {
      pages.push('...')
    }

    const start = Math.max(2, current - 1)
    const end = Math.min(total - 1, current + 1)

    for (let i = start; i <= end; i++) {
      pages.push(i)
    }

    if (current < total - 3) {
      pages.push('...')
    }

    if (total > 1) {
      pages.push(total)
    }
  }

  return pages
})

defineExpose({ reload, changePage, changePageSize, sortBy })

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
   successMessage.value = null
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
     successMessage.value = `Successfully added ${singularize(props.title)} to ${props.title}.`
     showModal.value = false
     resetForm()
     await reload()
     setTimeout(() => {
       successMessage.value = null
     }, 3000)
   } catch (e: unknown) {
    console.error(e)
    const error = e as { message?: string; response?: { status: number; data?: { error?: string; message?: string } } }
    if (error.response) {
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

// Edit functionality
function openEditModal(item: unknown) {
  editingItem.value = item
  const itemObj = item as Record<string, unknown>
  const fields = props.editFields || props.createFields

  const base: Record<string, unknown> = {}
  
  // Check if we need to split a datetime field into separate date and time fields
  let hasDateField = false
  let hasTimeField = false
  let dateFieldKey = ''
  let timeFieldKey = ''
  
  for (const field of fields) {
    if (field.type === 'date') {
      hasDateField = true
      dateFieldKey = field.key
    }
    if (field.type === 'time') {
      hasTimeField = true
      timeFieldKey = field.key
    }
  }
  
  // If we have both date and time fields, look for a combined datetime field in the data
  if (hasDateField && hasTimeField) {
    // Try to find a datetime field that could be split (e.g., 'startAt' for 'startDate' and 'startTime')
    const possibleDateTimeKeys = Object.keys(itemObj).filter(key => 
      key.toLowerCase().includes('at') || 
      key.toLowerCase().includes('datetime') ||
      (dateFieldKey.replace('Date', '') && key.toLowerCase().includes(dateFieldKey.replace('Date', '').toLowerCase()))
    )
    
    for (const dtKey of possibleDateTimeKeys) {
      const dtValue = itemObj[dtKey]
      if (dtValue && typeof dtValue === 'string') {
        // Parse the datetime string
        const dateTimePart = dtValue.split(' ')
        const isoDateTimePart = dtValue.split('T')
        
        if (dateTimePart.length >= 2) {
          // Format: "YYYY-MM-DD HH:MM:SS"
          base[dateFieldKey] = dateTimePart[0]
          const timePart = dateTimePart[1]?.split(':')
          if (timePart && timePart.length >= 2) {
            base[timeFieldKey] = `${timePart[0]}:${timePart[1]}`
          }
        } else if (isoDateTimePart.length >= 2) {
          // Format: "YYYY-MM-DDTHH:MM:SS.000Z"
          base[dateFieldKey] = isoDateTimePart[0]
          const timePart = isoDateTimePart[1]?.split(':')
          if (timePart && timePart.length >= 2) {
            base[timeFieldKey] = `${timePart[0]}:${timePart[1]}`
          }
        }
      }
    }
  }
  
  // Process all fields
  for (const field of fields) {
    // Skip if already populated from datetime splitting
    if ((field.key === dateFieldKey || field.key === timeFieldKey) && base[field.key]) {
      continue
    }
    
    if (itemObj[field.key] !== undefined) {
      // Convert datetime strings to date format for date inputs
      if (field.type === 'date' && itemObj[field.key]) {
        const dateValue = itemObj[field.key] as string
        // Extract just the date part (YYYY-MM-DD) from datetime string
        const datePart = dateValue.split(' ')[0]?.split('T')[0]
        base[field.key] = datePart || dateValue
      } else if (field.type === 'time' && itemObj[field.key]) {
        const timeValue = itemObj[field.key] as string
        // Extract time part (HH:MM) from datetime or time string
        const timeParts = timeValue.split(' ')
        const timeString = timeParts.length > 1 ? timeParts[1] : timeParts[0]
        const timeComponents = timeString?.split(':')
        if (timeComponents && timeComponents.length >= 2) {
          base[field.key] = `${timeComponents[0]}:${timeComponents[1]}`
        } else {
          base[field.key] = timeValue
        }
      } else {
        base[field.key] = itemObj[field.key]
      }
    } else if (props.initialValues && field.key in props.initialValues) {
      base[field.key] = props.initialValues[field.key]
    } else {
      base[field.key] = field.type === 'checkbox' ? false : null
    }
  }
  editForm.value = base
  showEditModal.value = true
  editErrorMessage.value = null
}

function closeEditModal() {
  showEditModal.value = false
  editingItem.value = null
  editForm.value = {}
}

async function submitEdit() {
   if (!editingItem.value || !props.onUpdate) return

   editSubmitting.value = true
   editErrorMessage.value = null
   successMessage.value = null

   try {
     const itemObj = editingItem.value as Record<string, unknown>
     const id = itemObj.id as number | string

     const fields = props.editFields || props.createFields
     for (const f of fields) {
       if (
         f.required &&
         (editForm.value[f.key] === null || editForm.value[f.key] === '' || editForm.value[f.key] === undefined)
       ) {
         throw new Error(`${f.label} is required`)
       }
     }

     await props.onUpdate(id, editForm.value)
     successMessage.value = `Successfully updated ${singularize(props.title)} in ${props.title}.`
     closeEditModal()
     await reload()
     setTimeout(() => {
       successMessage.value = null
     }, 3000)
   } catch (e: unknown) {
    console.error(e)
    const error = e as { message?: string; response?: { status: number; data?: { error?: string; message?: string } } }
    if (error.response) {
      if (error.response.status === 409) {
        editErrorMessage.value = error.response.data?.error ?? 'Conflict error'
      } else if (error.response.status === 422) {
        const data = error.response.data as { errors?: Record<string, string[]>, message?: string }
        const errors = data?.errors
        if (errors && typeof errors === 'object') {
          const messages = Object.values(errors).flat().join(', ')
          editErrorMessage.value = messages
        } else {
          editErrorMessage.value = data?.message ?? error.response.data?.error ?? 'Validation failed. Please check your input.'
        }
      } else if (error.response.status >= 500) {
        editErrorMessage.value = 'We encountered a technical issue while processing your request. Our team has been notified. Please try again in a few moments.'
      } else {
        editErrorMessage.value = 'An error occurred. Please check your input.'
      }
    } else {
      editErrorMessage.value = 'Network error. Please check your connection.'
    }
  } finally {
    editSubmitting.value = false
  }
}

// Delete functionality
function openDeleteModal(item: unknown) {
  deletingItem.value = item
  showDeleteModal.value = true
}

function closeDeleteModal() {
  showDeleteModal.value = false
  deletingItem.value = null
  deleteErrorMessage.value = null
}

async function confirmDelete() {
  if (!deletingItem.value || !props.onDelete) return

  deleteSubmitting.value = true
  deleteErrorMessage.value = null

  try {
    const itemObj = deletingItem.value as Record<string, unknown>
    const id = itemObj.id as number | string

    await props.onDelete(id)
    successMessage.value = `Successfully deleted ${singularize(props.title)} from ${props.title}.`
    closeDeleteModal()
    await reload()
    setTimeout(() => {
      successMessage.value = null
    }, 3000)
  } catch (error: unknown) {
    console.error(error)
    // Handle error if needed - show error message for category deletion
    const errorObj = error as { response?: { status: number; data?: { message?: string } } }
    if (errorObj.response?.status === 409) {
      // Show error message for category deletion conflict
      deleteErrorMessage.value = errorObj.response.data?.message || 'This category cannot be deleted because it contains products.'
      // Show alert for 3 seconds
      setTimeout(() => {
        deleteErrorMessage.value = null
      }, 3000)
    } else {
      alert('Failed to delete item. Please try again.')
    }
  } finally {
    deleteSubmitting.value = false
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
          <span class="record-count">{{ totalRecords || rows.length }} records</span>
        </div>
        <div class="header-actions">
          <slot name="header-extra"></slot>
          <button class="btn-add" @click="() => (showModal = true)">
            <i class="bi bi-plus-lg"></i>
            <span>Add New</span>
          </button>
         
           <slot name="header-extra"></slot>
             <button class="btn-refresh" @click="reload()">
               <i class="bi bi-arrow-clockwise"></i>Refresh
             </button>
        
        </div>
      </div>
    </div>

    <!-- Success Message -->
    <transition name="slide-down">
      <div v-if="successMessage" class="success-toast">
        <div class="toast-content">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ successMessage }}</span>
        </div>
        <button class="toast-close" @click="successMessage = null">
          <i class="bi bi-x"></i>
        </button>
      </div>
    </transition>

    <!-- Table Container -->
    <div class="table-container">
      <!-- Table -->
      <div class="table-wrapper">
        <table class="modern-table">
          <thead>
            <tr>
              <th
                v-for="c in columns"
                :key="c.key"
                @click="sortBy(c.key)"
                class="sortable-header"
                :class="{ 'is-active': sortColumn === c.key }"
              >
                <div class="th-content">
                  <span class="label-text">{{ c.label }}</span>
                  <div class="sort-indicator">
                    <!-- Option 1: Using arrow-up-short / arrow-down-short for minimal look -->
                    <i 
                      class="bi sort-icon"
                      :class="[
                        sortColumn === c.key && sortDirection === 'asc' 
                          ? 'bi-arrow-up-short is-active' 
                          : 'bi-arrow-up-short'
                      ]"
                    ></i>
                    <i 
                      class="bi sort-icon"
                      :class="[
                        sortColumn === c.key && sortDirection === 'desc' 
                          ? 'bi-arrow-down-short is-active' 
                          : 'bi-arrow-down-short'
                      ]"
                    ></i>
                  </div>
                </div>
              </th>
              <th class="actions-header">
                <div class="th-content">Actions</div>
              </th>
            </tr>
          </thead>

          <tbody>

            <!-- Loading State -->
            <tr v-if="loading">
              <td :colspan="columns.length + 1" >
                <div class="status-card loading">
                  <div class="spinner"></div>
                  <span>Loading data...</span>
                </div>
              </td>
            </tr>

            <!-- Error State -->
            <tr v-else-if="error">
              <td :colspan="columns.length + 1" class="status-cell">
                <div class="status-card error">
                  <i class="bi bi-exclamation-triangle-fill"></i>
                  <div>
                    <p class="error-title">{{ error }}</p>
                    <button class="btn-retry" @click="reload">
                      <i class="bi bi-arrow-clockwise"></i>
                      Try Again
                    </button>
                  </div>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-else-if="!loading && !error && rows.length === 0">
              <td :colspan="columns.length + 1" class="status-cell">
                <div class="status-card empty">
                  <i class="bi bi-inbox"></i>
                  <span>No data available</span>
                </div>
              </td>
            </tr>

            <tr v-else v-for="r in rows" :key="(r as any).id" class="table-row">
              <td v-for="c in columns" :key="c.key" :data-label="c.label" v-html="getFormattedCellValue(r, c)"></td>
              <td class="actions-cell">
                <slot name="actions" :row="r">
                  <div class="action-buttons">
                    <button
                      v-if="enableEdit"
                      class="btn-action edit"
                      @click="openEditModal(r)"
                      title="Edit"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button
                      v-if="enableDelete"
                      class="btn-action delete"
                      @click="openDeleteModal(r)"
                      title="Delete"
                    >
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

    <!-- Pagination -->
    <div v-if="enablePagination" class="pagination-container">
      <div class="pagination-controls">
        <!-- Page Size Selector -->
        <div class="page-size-selector">
          <label class="page-size-label">Show:</label>
          <select
            :value="pageSize"
            @change="changePageSize(Number(($event.target as HTMLSelectElement).value))"
            class="page-size-select"
          >
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="20">20</option>
          </select>
          <span class="page-size-text">per page</span>
        </div>

        <!-- Page Info -->
        <div class="page-info">
          <span>Page {{ currentPage }} of {{ totalPages }} ({{ totalRecords }} total)</span>
        </div>

        <!-- Navigation Buttons -->
        <div class="page-navigation">
          <button
            @click="changePage(1)"
            :disabled="currentPage === 1"
            class="btn-page-nav"
            title="First Page"
          >
            <i class="bi bi-chevron-double-left"></i>
          </button>
          <button
            @click="changePage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="btn-page-nav"
            title="Previous Page"
          >
            <i class="bi bi-chevron-left"></i>
          </button>

          <!-- Page Numbers -->
          <template v-for="page in visiblePages" :key="page">
            <button
              v-if="page === '...'"
              class="btn-page-number disabled"
              disabled
            >
              ...
            </button>
            <button
              v-else
              @click="changePage(page as number)"
              :class="['btn-page-number', { active: page === currentPage }]"
            >
              {{ page }}
            </button>
          </template>

          <button
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="btn-page-nav"
            title="Next Page"
          >
            <i class="bi bi-chevron-right"></i>
          </button>
          <button
            @click="changePage(totalPages)"
            :disabled="currentPage === totalPages"
            class="btn-page-nav"
            title="Last Page"
          >
            <i class="bi bi-chevron-double-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <teleport to="body">
      <transition name="modal">
        <div v-if="showModal" class="modal-overlay">
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
                <br>
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

    <!-- Edit Modal -->
    <teleport to="body" v-if="enableEdit">
      <transition name="modal">
        <div v-if="showEditModal" class="modal-overlay">
          <div class="modern-modal" @click.stop>
            <button class="modal-close" @click="closeEditModal">
              <i class="bi bi-x-lg"></i>
            </button>

            <div class="modal-header">
              <div class="modal-icon">
                <i class="bi bi-pencil-square"></i>
              </div>
              <h2 class="modal-title">{{ editTitle ?? `Edit ${title}` }}</h2>
            </div>

            <div class="modal-body">
              <!-- Error Message -->
              <transition name="fade">
                <div v-if="editErrorMessage" class="error-message">
                  <i class="bi bi-exclamation-circle-fill"></i>
                  <div class="error-content">
                    <p>{{ editErrorMessage }}</p>
                  </div>
                </div>
              </transition>

              <form @submit.prevent="submitEdit" class="modal-form">
                <div v-for="field in (editFields || createFields)" :key="field.key" class="form-group">
                  <label class="form-label">
                    {{ field.label }}
                    <span v-if="field.required" class="required-star">*</span>
                  </label>

                  <!-- Text Input -->
                  <template v-if="field.type === 'text'">
                    <input
                      v-model="editForm[field.key]"
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
                      v-model="editForm[field.key]"
                    />
                  </template>

                  <!-- Time Input -->
                  <template v-else-if="field.type === 'time'">
                    <input
                      type="time"
                      class="form-input"
                      v-model="editForm[field.key]"
                    />
                  </template>

                  <!-- DateTime Input -->
                  <template v-else-if="field.type === 'datetime-local'">
                    <input
                      type="datetime-local"
                      class="form-input"
                      v-model="editForm[field.key]"
                    />
                  </template>

                  <!-- Textarea -->
                  <template v-else-if="field.type === 'textarea'">
                    <textarea
                      v-model="editForm[field.key] as string"
                      class="form-input form-textarea"
                      rows="4"
                      :placeholder="`Enter ${field.label.toLowerCase()}`"
                    ></textarea>
                  </template>

                  <!-- Number Input -->
                  <template v-else-if="field.type === 'number'">
                    <input
                      v-model.number="editForm[field.key]"
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
                    <select v-model="editForm[field.key]" class="form-select">
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
                          v-model="editForm[field.key] as boolean"
                        />
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">{{ field.label }}</span>
                      </label>
                    </div>
                  </template>
                </div>

              </form>
            </div>

            <div class="modal-footer">
              <button
                type="button"
                class="btn-modal-secondary"
                :disabled="editSubmitting"
                @click="closeEditModal"
              >
                Cancel
              </button>
              <button
                type="button"
                class="btn-modal-primary"
                :disabled="editSubmitting"
                @click="submitEdit"
              >
                <span v-if="editSubmitting" class="spinner-small"></span>
                <i v-else class="bi bi-check-lg"></i>
                <span>Save</span>
              </button>
            </div>
          </div>
        </div>
      </transition>
    </teleport>

    <!-- Delete Modal -->
    <teleport to="body" v-if="enableDelete">
      <transition name="modal">
        <div v-if="showDeleteModal" class="modal-overlay">
          <div class="modern-modal" @click.stop>
            <button class="modal-close" @click="closeDeleteModal">
              <i class="bi bi-x-lg"></i>
            </button>

            <div class="modal-header">
              <div class="modal-icon">
                <i class="bi bi-exclamation-triangle"></i>
              </div>
              <h2 class="modal-title">{{ deleteTitle ?? 'Confirm Deletion' }}</h2>
            </div>

            <div class="modal-body">
              <!-- Error Message -->
              <transition name="fade">
                <div v-if="deleteErrorMessage" class="error-message">
                  <i class="bi bi-exclamation-circle-fill"></i>
                  <div class="error-content">
                    <p>{{ deleteErrorMessage }}</p>
                  </div>
                </div>
              </transition>

              <div class="delete-confirmation">
                <p>{{ deleteMessage ?? `Are you sure you want to delete this ${title.toLowerCase()}?` }}</p>
              </div>
            </div>

            <div class="modal-footer">
              <button
                type="button"
                class="btn-modal-secondary"
                :disabled="deleteSubmitting"
                @click="closeDeleteModal"
              >
                Cancel
              </button>
              <button
                type="button"
                class="btn-modal-danger"
                :disabled="deleteSubmitting"
                @click="confirmDelete"
              >
                <span v-if="deleteSubmitting" class="spinner-small"></span>
                <i v-else class="bi bi-trash"></i>
                <span>Delete</span>
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
  margin: 30px auto;
  width: 100%;
  max-width: 100%;
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
  flex-wrap: wrap;
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
  flex-wrap: wrap;
}

.btn-add,
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
  white-space: nowrap;
}

.btn-add:hover,
.btn-refresh:hover {
  background: #8C6353;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 40, 69, 0.2);
}

.btn-add i,
.btn-refresh i {
  font-size: 1rem;
}

/* Status Cards */
.status-card {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.25rem;
  padding: 2rem;
  border-radius: 16px;
  font-size: 1rem;
  flex: 1;
}

.status-cell {
  text-align: center;
  padding: 2rem 1rem;
}

.status-card.loading {
  background: white;
  color: #1A2845;
  justify-content: center;
}

.status-card.error {
  background: #fff5f5;
  color: #c53030;
  border: 2px solid #feb2b2;
}

.status-card.empty {
  background: white;
  color: #666;
  border: 2px solid #EEEAE4;
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
  display: flex;
  flex-direction: column;
}

.table-wrapper {
  overflow-x: auto;
  flex: 1;
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

.sortable-header {
  cursor: pointer;
  user-select: none;
  padding: 16px 20px;
  background-color: #EEEAE4;
  border-bottom: 2px solid #E7D7C9;
  position: relative;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-weight: 500;
  letter-spacing: 0.3px;
  text-align: left;
}

.sortable-header::before {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background: linear-gradient(90deg, #8C6353 0%, #1A2845 100%);
  transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.sortable-header:hover::before {
  width: 100%;
}

.sortable-header:hover {
  background-color: #E7D7C9;
  transform: translateY(-1px);
}

.sortable-header.is-active {
  color: #1A2845;
  font-weight: 600;
}

.sortable-header.is-active::before {
  width: 100%;
}

.th-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.label-text {
  color: #1A2845;
  font-size: 0.9rem;
  text-transform: uppercase;
  transition: color 0.3s ease;
}

.sortable-header:hover .label-text {
  color: #8C6353;
}

.sort-indicator {
  display: flex;
  flex-direction: column;
  gap: -4px;
  line-height: 0.5;
  opacity: 0.4;
  transition: opacity 0.3s ease;
}

.sortable-header:hover .sort-indicator {
  opacity: 0.7;
}

.sortable-header.is-active .sort-indicator {
  opacity: 1;
}

.sort-icon {
  color: #8C6353;
  font-size: 1.2rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  transform-origin: center;
}

.sort-icon.is-active {
  color: #1A2845;
  transform: scale(1.3);
  filter: drop-shadow(0 1px 2px rgba(26, 40, 69, 0.3));
  font-weight: bold;
}

.sortable-header:focus-visible {
  outline: 2px solid #8C6353;
  outline-offset: -2px;
  z-index: 1;
}

@keyframes sortPulse {
  0%, 100% { transform: scale(1.3); }
  50% { transform: scale(1.5); }
}

.sort-icon.is-active {
  animation: sortPulse 0.4s ease-out;
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
  display: flex;
  flex-direction: column;
  align-items: center;
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

.delete-confirmation {
  text-align: center;
}

.delete-confirmation p {
  margin: 0 0 1rem 0;
  color: #1A2845;
  font-size: 1rem;
}

.warning-text {
  color: #c53030;
  font-weight: 600;
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
.btn-modal-primary,
.btn-modal-danger {
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

.btn-modal-danger {
  background: #FF4D5A;
  color: white;
}

.btn-modal-danger:hover:not(:disabled) {
  background: #E63946;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(197, 48, 48, 0.2);
}

.btn-modal-secondary:disabled,
.btn-modal-primary:disabled,
.btn-modal-danger:disabled {
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

/* Success Toast */
.success-toast {
  position: fixed;
  top: 20px;
  right: 20px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
  z-index: 1050;
  min-width: 350px;
  max-width: 500px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
}

.toast-content i {
  font-size: 1.25rem;
  flex-shrink: 0;
}

.toast-content span {
  font-weight: 600;
  font-size: 0.95rem;
  line-height: 1.4;
}

.toast-close {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 50%;
  transition: all 0.2s ease;
  flex-shrink: 0;
  opacity: 0.8;
}

.toast-close:hover {
  background: rgba(255, 255, 255, 0.2);
  opacity: 1;
  transform: scale(1.1);
}

.toast-close i {
  font-size: 1rem;
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

.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-100%) scale(0.9);
}

/* Pagination */
.pagination-container {
  margin-top: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 16px;
  border: 2px solid #EEEAE4;
}

.pagination-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.page-size-selector {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.page-size-label {
  font-weight: 600;
  color: #1A2845;
  font-size: 0.875rem;
}

.page-size-select {
  padding: 0.375rem 0.75rem;
  border: 2px solid #EEEAE4;
  border-radius: 8px;
  background: white;
  color: #1A2845;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.page-size-select:focus {
  border-color: #8C6353;
  box-shadow: 0 0 0 4px rgba(140, 99, 83, 0.1);
}

.page-size-text {
  color: #666;
  font-size: 0.875rem;
}

.page-info {
  font-size: 0.875rem;
  color: #666;
  font-weight: 500;
}

.page-navigation {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.btn-page-nav,
.btn-page-number {
  width: 36px;
  height: 36px;
  border: 2px solid #EEEAE4;
  background: white;
  color: #1A2845;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-page-nav:hover:not(:disabled) {
  background: #EEEAE4;
  border-color: #8C6353;
  color: #8C6353;
  transform: scale(1.05);
}

.btn-page-number:hover:not(.active):not(.disabled) {
  background: #EEEAE4;
  border-color: #8C6353;
  color: #8C6353;
}

.btn-page-number.active {
  background: #1A2845;
  color: white;
  border-color: #1A2845;
}

.btn-page-nav:disabled,
.btn-page-number.disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Mobile Responsive Design */
@media (max-width: 768px) {
  .admin-container {
    padding: 1rem;
    border-radius: 16px;
    margin: 15px;
    width: calc(100% - 30px);
  }

  .admin-header {
    margin-bottom: 1.5rem;
  }

  .header-content {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .title-section {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
  }

  .admin-title {
    font-size: 1.35rem;
    line-height: 1.2;
  }

  .record-count {
    padding: 0.35rem 0.75rem;
    font-size: 0.8125rem;
    white-space: nowrap;
  }

  .header-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.625rem;
    width: 100%;
  }

    .btn-add,
    .btn-refresh {
      width: 100%;
      padding: 0.75rem 0.5rem; 
      font-size: 0.875rem;
      justify-content: center;
      min-height: 44px;
      gap: 0.375rem; /* Reduce gap between icon and text */

    }

    .btn-add i,
    .btn-refresh i {
      font-size: 0.875rem; 
      flex-shrink: 0;
    }
    .btn-add span,
    .btn-refresh span {
      white-space: nowrap; /* Prevent text wrapping */
    }


  /* Card-based table layout for mobile */
  .table-container {
    border-radius: 12px;
  }

  .modern-table thead {
    display: none;
  }

  .modern-table,
  .modern-table tbody,
  .modern-table tr,
  .modern-table td {
    display: block;
    width: 100%;
  }

  .modern-table tbody {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
    padding: 0.75rem;
  }

  .modern-table tr {
    display: flex;
    flex-direction: column;
    border: 2px solid #EEEAE4;
    border-radius: 12px;
    padding: 1rem;
    background: white;
    box-shadow: 0 2px 8px rgba(26, 40, 69, 0.06);
    margin-bottom: 0;
  }

  .modern-table tr:hover {
    background: linear-gradient(135deg, rgba(231, 215, 201, 0.08) 0%, rgba(238, 234, 228, 0.08) 100%);
    box-shadow: 0 4px 12px rgba(26, 40, 69, 0.1);
  }

  .modern-table td {
    padding: 0.625rem 0;
    border: none;
    display: grid;
    grid-template-columns: 110px 1fr;
    gap: 0.75rem;
    align-items: start;
    font-size: 0.875rem;
  }

  .modern-table td::before {
    content: attr(data-label);
    font-weight: 700;
    color: #8C6353;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
  }

  .modern-table td:not(:last-child) {
    border-bottom: 1px solid #EEEAE4;
    padding-bottom: 0.625rem;
  }

  .modern-table td:not(:first-child) {
    padding-top: 0.625rem;
  }

  .actions-cell {
    text-align: left;
    padding-top: 0.875rem !important;
    border-top: 2px solid #EEEAE4 !important;
    margin-top: 0.25rem;
    grid-template-columns: 110px 1fr !important;
  }

  .actions-cell::before {
    content: 'Actions';
  }

  .action-buttons {
    justify-content: flex-start;
    gap: 0.625rem;
  }

  .btn-action {
    width: 42px;
    height: 42px;
    font-size: 1rem;
  }

  /* Pagination mobile styles */
  .pagination-container {
    margin-top: 1.5rem;
    padding: 1rem;
  }

  .pagination-controls {
    flex-direction: column;
    gap: 1rem;
  }

  .page-size-selector {
    width: 100%;
    justify-content: space-between;
    padding: 0.75rem;
    background: linear-gradient(135deg, #EEEAE4 0%, #E7D7C9 100%);
    border-radius: 10px;
  }

  .page-size-label,
  .page-size-text {
    font-size: 0.8125rem;
  }

  .page-size-select {
    padding: 0.5rem 0.625rem;
    font-size: 0.8125rem;
  }

  .page-info {
    text-align: center;
    width: 100%;
    font-size: 0.8125rem;
    padding: 0.5rem;
    background: #EEEAE4;
    border-radius: 8px;
    color: #1A2845;
    font-weight: 600;
  }

  .page-navigation {
    width: 100%;
    justify-content: center;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding: 0.5rem 0;
    scrollbar-width: thin;
    scrollbar-color: #8C6353 #EEEAE4;
  }

  .page-navigation::-webkit-scrollbar {
    height: 6px;
  }

  .page-navigation::-webkit-scrollbar-track {
    background: #EEEAE4;
    border-radius: 3px;
  }

  .page-navigation::-webkit-scrollbar-thumb {
    background: #8C6353;
    border-radius: 3px;
  }

  .btn-page-nav,
  .btn-page-number {
    min-width: 36px;
    flex-shrink: 0;
  }

  /* Modal mobile styles */
  .modal-overlay {
    padding: 0;
    align-items: center;
  }

  .modern-modal {
    max-height: 90vh;
    border-radius: 20px;
    width: 90%;
    max-width: 500px;
    margin-bottom: 200px;
  }

  

  .modal-header {
    padding: 1.75rem 1.25rem 1rem;
  }

  .modal-icon {
    width: 56px;
    height: 56px;
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
  }

  .modal-title {
    font-size: 1.25rem;
  }

  .modal-close {
    top: 1rem;
    right: 1rem;
    width: 36px;
    height: 36px;
  }

  .modal-body {
    padding: 1.5rem 1.25rem;
  }

  .modal-form {
    gap: 1.25rem;
  }

  .form-label {
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
  }

  .form-input,
  .form-select {
    padding: 0.75rem 0.875rem;
    font-size: 0.9375rem;
  }

  .modal-footer {
    padding: 1rem 1.25rem;
    gap: 0.75rem;
  }

  .btn-modal-secondary,
  .btn-modal-primary,
  .btn-modal-danger {
    padding: 0.8125rem 1rem;
    font-size: 0.9375rem;
  }

  /* Success toast mobile */
  .success-toast {
    top: 10px;
    right: 10px;
    left: 10px;
    min-width: auto;
    max-width: none;
    padding: 0.875rem 1rem;
  }

  .toast-content span {
    font-size: 0.875rem;
  }

  .toast-content i {
    font-size: 1.125rem;
  }

  /* Status cards mobile */
  .status-card {
    padding: 1.5rem;
    font-size: 0.9375rem;
  }

  .status-card i {
    font-size: 1.5rem;
  }

  .error-message {
    padding: 0.875rem 1rem;
    gap: 0.75rem;
  }

  .error-message i {
    font-size: 1.125rem;
  }

  .error-content p {
    font-size: 0.875rem;
  }

  .btn-retry-inline {
    padding: 0.5rem 0.875rem;
    font-size: 0.8125rem;
  }
}

/* Extra small devices (phones in portrait, less than 576px) */
@media (max-width: 575px) {
  .admin-container {
    padding: 0.875rem;
    margin: 10px;
    width: calc(100% - 20px);
    border-radius: 12px;
  }

  .admin-title {
    font-size: 1.25rem;
  }

  .record-count {
    padding: 0.3rem 0.625rem;
    font-size: 0.75rem;
  }

  .modern-table tbody {
    padding: 0.5rem;
    gap: 0.75rem;
  }

  .modern-table tr {
    padding: 0.875rem;
  }

  .modern-table td {
    grid-template-columns: 100px 1fr;
    gap: 0.625rem;
    font-size: 0.8125rem;
  }

  .modern-table td::before {
    font-size: 0.6875rem;
  }

  .actions-cell {
    grid-template-columns: 100px 1fr !important;
  }

  .btn-action {
    width: 38px;
    height: 38px;
    font-size: 0.9375rem;
  }

  .page-navigation {
    gap: 0.375rem;
  }

  .btn-page-nav,
  .btn-page-number {
    min-width: 32px;
    height: 32px;
    font-size: 0.8125rem;
  }
}
</style>