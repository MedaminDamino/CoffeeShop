import { api } from './client'

export interface BranchDTO {
  id: number
  name: string
  address?: string
  phone?: string
}

interface BranchRaw {
  id: number
  branch_name: string
  branch_address?: string
  branch_phone?: string
}

export function getBranches(params?: { page?: number; per_page?: number }) {
  return api.get('/branches', { params }).then((r) => {
    const data = r.data;
    if (data && typeof data === 'object' && 'data' in data) {
      // Paginated response
      return {
        ...data,
        data: data.data.map((branch: BranchRaw) => ({
          id: branch.id,
          name: branch.branch_name,
          address: branch.branch_address,
          phone: branch.branch_phone
        }))
      };
    } else {
      // Regular array response
      return data.map((branch: BranchRaw) => ({
        id: branch.id,
        name: branch.branch_name,
        address: branch.branch_address,
        phone: branch.branch_phone
      }));
    }
  });
}
export function getBranch(id: number) {
  return api.get<{ id: number; branch_name: string; branch_address?: string; branch_phone?: string }>(`/branches/${id}`)
    .then((r) => ({
      id: r.data.id,
      name: r.data.branch_name,
      address: r.data.branch_address,
      phone: r.data.branch_phone
    }))
}
export function createBranch(data: Pick<BranchDTO, 'name' | 'address' | 'phone'>) {
  const apiPayload = {
    branch_name: data.name,
    branch_address: data.address,
    branch_phone: data.phone
  }
  return api.post<{ id: number; branch_name: string; branch_address?: string; branch_phone?: string }>('/branches', apiPayload)
    .then((r) => ({
      id: r.data.id,
      name: r.data.branch_name,
      address: r.data.branch_address,
      phone: r.data.branch_phone
    }))
}
export function updateBranch(id: number, data: Partial<Pick<BranchDTO, 'name' | 'address' | 'phone'>>) {
  const apiPayload: Record<string, string | undefined> = {}
  if (data.name !== undefined) apiPayload.branch_name = data.name
  if (data.address !== undefined) apiPayload.branch_address = data.address
  if (data.phone !== undefined) apiPayload.branch_phone = data.phone
  return api.put<{ id: number; branch_name: string; branch_address?: string; branch_phone?: string }>(`/branches/${id}`, apiPayload)
    .then((r) => ({
      id: r.data.id,
      name: r.data.branch_name,
      address: r.data.branch_address,
      phone: r.data.branch_phone
    }))
}
export function deleteBranch(id: number) {
  return api.delete(`/branches/${id}`)
}
