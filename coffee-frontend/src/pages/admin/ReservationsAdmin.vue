<script setup lang="ts">
import CrudTable from '@/components/admin/CrudTable.vue'
import { getReservations, createReservation } from '@/api/reservations'
import { getTables, type TableDTO } from '@/api/tables'
import { getUsers, type User as UserDTO } from '@/api/users'
import { onMounted, ref, computed } from 'vue'

const tables = ref<TableDTO[]>([])
const users = ref<UserDTO[]>([])
onMounted(async () => {
  tables.value = await getTables()
  users.value = await getUsers()
})

function formatDateTime(value: string | unknown) {
  if (!value) return ''
  const date = new Date(value as string)
  return date.toLocaleString('en-GB', { // en-GB gives DD/MM/YYYY
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  })
}
  
const reservationFields = computed(() => [
  { key: 'userId', label: 'User ID', type: 'select' as const, required: true, min: 1,
    options: users.value.map((u) => ({ label: `${u.name} (ID: ${u.id})`, value: u.id })), 
   },
  {
    key: 'tableId',
    label: 'Table',
    type: 'select' as const,
    required: true,
    options: tables.value.map((t) => ({ label: `Table ${t.number} (${t.capacity} seats)`, value: t.id })),
  },
  { key: 'startDate', label: 'Start Date', type: 'date' as const, required: true },
  { key: 'startTime', label: 'Start Time', type: 'time' as const, required: true },

  {
    key: 'status',
    label: 'Status',
    type: 'select' as const,
    options: [
      { label: 'Pending', value: 'pending' },
      { label: 'Confirmed', value: 'confirmed' },
      { label: 'Canceled', value: 'canceled' },
      { label: 'Completed', value: 'completed' }
    ]
  },
  { key: 'notes', label: 'Notes', type: 'textarea' as const },
])
</script>

<template>
  <CrudTable
    title="Reservations"
  :columns="[
    { key: 'id', label: 'ID' },
    { key: 'userId', label: 'User ID' },
    { key: 'tableId', label: 'Table ID' },
    { 
      key: 'startAt', 
      label: 'Start At', 
      formatter: (value) => formatDateTime(value as string) 
    },
  { key: 'status', label: 'Status' },
]"

    :fetchAll="getReservations"
    :createFields="reservationFields"
    :onCreate="(payload) => {
      const transformed = {
         userId: payload.userId as number,
         tableId: payload.tableId as number,
         startAt: (typeof payload.startDate === 'string' && typeof payload.startTime === 'string')
          ? `${payload.startDate} ${payload.startTime}:00`
          : '',
         status: payload.status as 'pending' | 'confirmed' | 'canceled' | 'completed',
         notes: payload.notes as string,
       }
      return createReservation(transformed)
    }"
  />
</template>


