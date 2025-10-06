<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'

interface Column<T = unknown> {
  key: string
  label: string
  formatter?: (value: T) => string       
}

type FieldType = 'text' | 'number' | 'textarea' | 'select' | 'checkbox' | 'datepicker' | 'datetime-local' | 'date' | 'time'

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
    error.value = 'Failed to load'
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
    // Ensure form stays in sync when schema changes (e.g., dynamic select options)
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
    // Basic required validation client-side
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
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h5 m-0">{{ title }}</h1>
      <button class="btn btn-primary" @click="() => (showModal = true)">
        <i class="bi bi-plus-circle me-2"></i>Add
      </button>
    </div>

    <div v-if="loading" class="alert alert-info">Loading...</div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
    <div v-else>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th v-for="c in columns" :key="c.key">{{ c.label }}</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in rows" :key="(r as any).id">
              <td v-for="c in columns" :key="c.key">{{ getFormattedCellValue(r, c) }}</td>
              <td class="text-end">
                <button class="btn btn-light btn-sm me-1" disabled>
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-outline-danger btn-sm" disabled>
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal fade show" tabindex="-1" style="display: block" v-if="showModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ createTitle ?? `Add ${title}` }}</h5>
            <button type="button" class="btn-close" @click="showModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3" v-for="field in createFields" :key="field.key">
              <label class="form-label">{{ field.label }}</label>

              <template v-if="field.type === 'text'">
                <input v-model="form[field.key]" class="form-control" />
              </template>

              <template v-else-if="field.type === 'date'">
                <input
                  type="date"
                  class="form-control"
                  v-model="form[field.key]"
                />
              </template>

              <template v-else-if="field.type === 'time'">
                <input
                  type="time"
                  class="form-control"
                  v-model="form[field.key]"
                />
              </template>

              <template v-else-if="field.type === 'datetime-local'">
                <input
                  type="datetime-local"
                  class="form-control"
                  v-model="form[field.key]"
                />
              </template>

              <template v-else-if="field.type === 'textarea'">
                <textarea
                  v-model="form[field.key] as string"
                  class="form-control"
                  rows="3"
                ></textarea>
              </template>

              <template v-else-if="field.type === 'number'">
                <input
                  v-model.number="form[field.key]"
                  type="number"
                  class="form-control"
                  :min="field.min"
                  :max="field.max"
                  :step="field.step ?? 1"
                />
              </template>

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

              <template v-else-if="field.type === 'checkbox'">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    v-model="form[field.key] as boolean"
                    :id="field.key"
                  />
                  <label class="form-check-label" :for="field.key">{{ field.label }}</label>
                </div>
              </template>
            </div>
            <div v-if="errorMessage" class="alert alert-danger mt-3">
              {{ errorMessage }}
              <button v-if="showRetry" @click="submit" :disabled="submitting" class="btn btn-sm btn-outline-danger mt-2">Retry</button>
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-light"
              :disabled="submitting"
              @click="showModal = false"
            >
              Cancel
            </button>
            <button type="button" class="btn btn-primary" :disabled="submitting" @click="submit">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-if="showModal"></div>
  </div>
</template>

