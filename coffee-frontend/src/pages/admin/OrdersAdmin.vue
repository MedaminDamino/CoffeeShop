<script setup lang="ts">
import CrudTable from '@/components/admin/CrudTable.vue'
import { getOrders, createOrder, type OrderDTO } from '@/api/orders'
import { getBranches, type BranchDTO } from '@/api/branches'
import { onMounted, ref, computed } from 'vue'

const branches = ref<BranchDTO[]>([])
onMounted(async () => {
  branches.value = await getBranches()
})

const orderFields = computed(() => [
  { key: 'userId', label: 'User ID', type: 'number' as const, required: true, min: 1 },
  {
    key: 'branchId',
    label: 'Branch',
    type: 'select' as const,
    required: true,
    options: branches.value.map((b) => ({ label: b.name, value: b.id })),
  },
  { key: 'totalAmount', label: 'Total Amount', type: 'number' as const, required: true, min: 0, step: 0.01 },
  {
    key: 'status',
    label: 'Status',
    type: 'select' as const,
    options: [
      { label: 'Pending', value: 'pending' },
      { label: 'Paid', value: 'paid' },
      { label: 'Cancelled', value: 'cancelled' }
    ]
  },
  {
    key: 'paymentMethod',
    label: 'Payment Method',
    type: 'select' as const,
    options: [
      { label: 'Cash', value: 'cash' },
      { label: 'Online', value: 'online' }
    ]
  },
])
</script>

<template>
  <CrudTable
    title="Orders"
    :columns="[
      { key: 'id', label: 'ID' },
      { key: 'userId', label: 'User ID' },
      { key: 'totalAmount', label: 'Total Amount' },
      { key: 'status', label: 'Status' },
    ]"
    :fetchAll="getOrders"
    :createFields="orderFields"
    :onCreate="(payload) => createOrder(payload as Pick<OrderDTO, 'userId' | 'branchId' | 'totalAmount' | 'status' | 'paymentMethod'>)"
    :enablePagination="true"
    :defaultPageSize="5"
  />
</template>
