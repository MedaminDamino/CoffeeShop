<script setup lang="ts">
import CrudTable from '@/components/admin/CrudTable.vue'
import { getOrders, createOrder, updateOrder, deleteOrder } from '@/api/orders'
import { getBranches, type BranchDTO } from '@/api/branches'
import { onMounted, ref, computed } from 'vue'
import { getUsers, type User as UserDTO } from '@/api/users'
import { getProducts } from '@/api/products'
import type { Product } from '@/interfaces/Product'


const branches = ref<BranchDTO[]>([])
const users = ref<UserDTO[]>([])
const products = ref<Product[]>([])

onMounted(async () => {
   branches.value = await getBranches()
    users.value = await getUsers()
    products.value = await getProducts({ show_all: 'true' })
})


const orderFields = computed(() => [
   { key: 'userId',
   label: 'User ID',
   type: 'select' as const,
   required: true, min: 1,
   options: users.value.map((u) => ({ label: u.name, value: u.id }))
 },
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
   {
     key: 'products',
     label: 'Products',
     type: 'textarea' as const,
     required: false,
     placeholder: 'JSON format: [{"product_id": 1, "quantity": 2, "price": 5.99}]'
   },
 ])

const editOrderFields = computed(() => [
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
     required: true,
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
   {
     key: 'products',
     label: 'Products',
     type: 'textarea' as const,
     required: false,
     placeholder: 'JSON format: [{"product_id": 1, "quantity": 2, "price": 5.99}]'
   },
 ])

const tableColumns = computed(() => [
  { key: 'id', label: 'ID' },
  { key: 'userId', label: 'User ID' },
  { key: 'totalAmount', label: 'Total Amount' },
  { key: 'status', label: 'Status' },
  { key: 'products', label: 'Products', formatter: (value: unknown) => {
    if (!value || !Array.isArray(value)) return 'No products'
    const prods = products.value
    return (value as Array<{product_id: number; quantity: number; price: number}>).map((p) => `${p.quantity}x ${prods.find((prod: Product) => prod.id === p.product_id)?.name || 'Unknown'} ($${p.price})`).join(', ')
  }},
])
</script>

<template>
  <CrudTable
    title="Orders"
    :columns="tableColumns"
    :fetchAll="getOrders"
    :createFields="orderFields"
    :editFields="editOrderFields"
    :onCreate="(payload) => {
      const parsedProducts = payload.products ? JSON.parse(payload.products as string) : undefined
      return createOrder({
        userId: payload.userId as number,
        branchId: payload.branchId as number,
        totalAmount: payload.totalAmount as number,
        status: payload.status as 'pending' | 'paid' | 'cancelled',
        paymentMethod: payload.paymentMethod as 'cash' | 'online',
        products: parsedProducts
      })
    }"
    :enableEdit="true"
    :enableDelete="true"
    :editTitle="'Edit Order'"
    :deleteTitle="'Delete Order'"
    :deleteMessage="'Are you sure you want to delete this Order? This action cannot be undone.'"
    :onUpdate="(id, payload) => {
       const parsedProducts = payload.products ? JSON.parse(payload.products as string) : undefined
       const transformed = {
          branchId: payload.branchId as number,
          totalAmount: payload.totalAmount as number,
          status: payload.status as 'pending' | 'paid' | 'cancelled' ,
          paymentMethod: payload.paymentMethod as 'cash' | 'online',
          products: parsedProducts
        }
       return updateOrder(id as number, transformed)
     }"
    :onDelete="(id) => deleteOrder(id as number)"
    :enablePagination="true"
    :defaultPageSize="5"
  />
</template>

