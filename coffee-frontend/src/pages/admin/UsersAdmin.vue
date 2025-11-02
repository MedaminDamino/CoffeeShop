<template>
  <CrudTable
    ref="crudTable"
    title="Users"
    :columns="columns"
    :fetchAll="getUsers"
    :createFields="createFields"
    :onCreate="(payload) => createUser(payload as Pick<User & { password: string }, 'name' | 'username' | 'email' | 'password' | 'role' | 'birthday'>)"
    :enablePagination="true"
    :defaultPageSize="5"
  >
    <template #actions="{ row }">
      <div class="action-buttons">
        <select
          v-if="canChangeRole(row as User)"
          :value="(row as User).role"
          @change="changeRole(row as User, $event)"
          class="form-select form-select-sm"
          :disabled="updatingUser === (row as User).id"
        >
          <option value="user">User</option>
          <option value="admin">Admin</option>
          <option v-if="currentUserRole === 'super_admin'" value="super_admin">Super Admin</option>
        </select>
        <span v-else class="text-muted small">Cannot modify</span>

        <button
          v-if="canDeleteUser(row as User)"
          class="btn btn-sm btn-outline-danger ms-2"
          @click="confirmDeleteUser(row as User)"
          :disabled="deletingUser === (row as User).id"
        >
          <i class="bi bi-trash"></i> Supprimer
        </button>
      </div>
    </template>
  </CrudTable>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import CrudTable from '@/components/admin/CrudTable.vue'
import { getUsers, createUser, updateUserRole, deleteUser, type User } from '@/api/users'
import { useAuthStore } from '@/stores/auth'

const crudTable = ref()

const authStore = useAuthStore()
const updatingUser = ref<number | null>(null)
const deletingUser = ref<number | null>(null)

const currentUserRole = computed(() => authStore.user?.role)

const canChangeRole = (user: User) => {
  // Super admin can change anyone's role
  if (currentUserRole.value === 'super_admin') {
    return user.id !== authStore.user?.id // Can't change own role
  }
  // Admin cannot change roles
  return false
}

const canDeleteUser = (user: User) => {
  // Super admin can delete anyone except themselves
  if (currentUserRole.value === 'super_admin') {
    return user.id !== authStore.user?.id
  }
  // Admin cannot delete users
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
    // error.value = 'Failed to update role'
    target.value = user.role // Reset select
  } finally {
    updatingUser.value = null
  }
}

const confirmDeleteUser = async (user: User) => {
  if (!confirm(`Are you sure you want to delete user "${user.name}" (${user.email})? This action cannot be undone.`)) {
    return
  }

  deletingUser.value = user.id
  try {
    await deleteUser(user.id)
    // Refresh the table
    crudTable.value?.reload()
  } catch (error) {
    console.error('Failed to delete user:', error)
    alert('Failed to delete user. Please try again.')
  } finally {
    deletingUser.value = null
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

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'username', label: 'Username' },
  { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role', formatter: (value: unknown) => `<span class="badge ${getRoleBadgeClass(value as string)}">${value}</span>` },
  { key: 'birthday', label: 'Birthday', formatter: (value: unknown) => value ? formatDate(value as string) : 'N/A' },
  { key: 'created_at', label: 'Created', formatter: (value: unknown) => formatDate(value as string) },
]

const createFields = [
  { key: 'name', label: 'Name', type: 'text' as const, required: true },
  { key: 'username', label: 'Username', type: 'text' as const, required: true },
  { key: 'email', label: 'Email', type: 'text' as const, required: true },
  { key: 'password', label: 'Password', type: 'password' as const, required: true },
  { key: 'role', label: 'Role', type: 'select' as const, required: true, options: [
    { label: 'User', value: 'user' },
    { label: 'Admin', value: 'admin' },
    { label: 'Super Admin', value: 'super_admin' }
  ] },
  { key: 'birthday', label: 'Birthday', type: 'date' as const },
]
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