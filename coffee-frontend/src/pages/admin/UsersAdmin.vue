<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h5 m-0">Users</h1>
      <button class="btn btn-primary" @click="loadUsers">
        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
      </button>
    </div>

    <div v-if="loading" class="alert alert-info">Loading...</div>

    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

    <div v-else>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Birthday</th>
              <th>Created</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.id }}</td>
            <td>{{ user.username }}</td>
            <td>{{ user.email }}</td>
            <td>
              <span :class="getRoleBadgeClass(user.role)" class="badge">
                {{ user.role }}
              </span>
            </td>
            <td>{{ user.birthday ? formatDate(user.birthday) : 'N/A' }}</td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td>
              <select
                v-if="canChangeRole(user)"
                :value="user.role"
                @change="changeRole(user, $event)"
                class="form-select form-select-sm"
                :disabled="updatingUser === user.id"
              >
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option v-if="currentUserRole === 'super_admin'" value="super_admin">Super Admin</option>
              </select>
              <span v-else class="text-muted small">Cannot modify</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    </div>

    <div v-if="!loading && users.length === 0" class="text-center py-4">
      <p class="text-muted">No users found.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { getUsers, updateUserRole, type User } from '@/api/users'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const users = ref<User[]>([])
const loading = ref(false)
const error = ref('')
const updatingUser = ref<number | null>(null)

const currentUserRole = computed(() => authStore.user?.role)

const loadUsers = async () => {
  loading.value = true
  error.value = ''
  try {
    users.value = await getUsers()
  } catch {
    error.value = 'Failed to load users'
  } finally {
    loading.value = false
  }
}

const canChangeRole = (user: User) => {
  // Super admin can change anyone's role
  if (currentUserRole.value === 'super_admin') {
    return user.id !== authStore.user?.id // Can't change own role
  }
  // Admin cannot change roles
  return false
}

const changeRole = async (user: User, event: Event) => {
  const target = event.target as HTMLSelectElement
  const newRole = target.value

  if (newRole === user.role) return

  updatingUser.value = user.id
  try {
    await updateUserRole(user.id, newRole)
    user.role = newRole
  } catch {
    error.value = 'Failed to update role'
    target.value = user.role // Reset select
  } finally {
    updatingUser.value = null
  }
}

const getRoleBadgeClass = (role: string) => {
  switch (role) {
    case 'super_admin':
      return 'bg-danger'
    case 'admin':
      return 'bg-warning text-dark'
    case 'user':
      return 'bg-secondary'
    default:
      return 'bg-secondary'
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString()
}

onMounted(() => {
  loadUsers()
})
</script>

<style scoped>
.table th {
  vertical-align: middle;
}

.table td {
  vertical-align: middle;
}

.form-select-sm {
  width: auto;
  min-width: 120px;
}
</style>