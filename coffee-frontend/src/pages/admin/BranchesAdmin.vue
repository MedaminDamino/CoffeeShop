<script setup lang="ts">
import CrudTable from '@/components/admin/CrudTable.vue'
import { getBranches, createBranch, updateBranch, deleteBranch, type BranchDTO } from '@/api/branches'
</script>

<template>
  <CrudTable
    title="Branches"
    :columns="[
      { key: 'id', label: 'ID' },
      { key: 'name', label: 'Name' },
      { key: 'address', label: 'Address' },
      { key: 'phone', label: 'Phone' },
    ]"
    :fetchAll="getBranches"
    :createFields="[
      { key: 'name', label: 'Name', type: 'text', required: true },
      { key: 'address', label: 'Address', type: 'text' },
      { key: 'phone', label: 'Phone', type: 'text' },
    ]"
    :onCreate="(payload) => createBranch(payload as Pick<BranchDTO, 'name' | 'address' | 'phone'>)"
    :enableEdit="true"
    :enableDelete="true"
    :editTitle="'Edit Branch'"
    :deleteTitle="'Delete Branch'"
    :deleteMessage="'Are you sure you want to delete this branch? This action cannot be undone.'"
    :onUpdate="(id, payload) => updateBranch(id as number, payload as Partial<Pick<BranchDTO, 'name' | 'address' | 'phone'>>)"
    :onDelete="(id) => deleteBranch(id as number)"
    :enablePagination="true"
    :defaultPageSize="5"
  />
</template>
