<script setup lang="ts">
import { onMounted, ref } from 'vue'

interface Column {
  key: string
  label: string
}
const props = defineProps<{
  title: string
  columns: Column[]
  fetchAll: () => Promise<unknown[]>
  onCreate?: () => void
}>()

const rows = ref<unknown[]>([])

function getCellValue(row: unknown, key: string) {
  const anyRow = row as Record<string, unknown>
  return anyRow
    ? (anyRow[key] as unknown as string | number | boolean | null | undefined)
    : undefined
}
const loading = ref(false)
const error = ref<string | null>(null)

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
  await reload()
})

defineExpose({ reload })
</script>

<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h5 m-0">{{ title }}</h1>
      <button v-if="onCreate" class="btn btn-primary" @click="onCreate">
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
              <td v-for="c in columns" :key="c.key">{{ getCellValue(r, c.key) }}</td>
              <td class="text-end">
                <button class="btn btn-light btn-sm me-1"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
