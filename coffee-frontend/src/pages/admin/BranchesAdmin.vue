<script setup lang="ts">
import CrudTable from '@/components/admin/CrudTable.vue'
import { getBranches, createBranch, type BranchDTO } from '@/api/branches'
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
  />
</template>
