import { api } from './client'

export interface TableDTO {
  id: number
  number: string
  capacity: number
  status: 'available' | 'reserved' | 'out_of_service'
  branchId: number
}
export function getTables() {
  return api.get<{ id: number; table_number: string; capacity: number; status: 'available' | 'reserved' | 'out_of_service'; branch_id: number }[]>('/tables')
    .then((r) => r.data.map(t => ({
      id: t.id,
      number: t.table_number,
      capacity: t.capacity,
      status: t.status,
      branchId: t.branch_id
    })))
}
export function getTable(id: number) {
  return api.get<{ id: number; table_number: string; capacity: number; status: 'available' | 'reserved' | 'out_of_service'; branch_id: number }>(`/tables/${id}`)
    .then((r) => ({
      id: r.data.id,
      number: r.data.table_number,
      capacity: r.data.capacity,
      status: r.data.status,
      branchId: r.data.branch_id
    }))
}
export function createTable(data: Pick<TableDTO, 'number' | 'capacity' | 'status' | 'branchId'>) {
  const apiPayload = {
    table_number: data.number,
    capacity: data.capacity,
    status: data.status,
    branch_id: data.branchId
  }
  return api.post<{ id: number; table_number: string; capacity: number; status: 'available' | 'reserved' | 'out_of_service'; branch_id: number }>('/tables', apiPayload)
    .then((r) => ({
      id: r.data.id,
      number: r.data.table_number,
      capacity: r.data.capacity,
      status: r.data.status,
      branchId: r.data.branch_id
    }))
}
export function updateTable(id: number, data: Partial<Pick<TableDTO, 'number' | 'capacity' | 'status' | 'branchId'>>) {
  const apiPayload: Record<string, string | number> = {}
  if (data.number !== undefined) apiPayload.table_number = data.number
  if (data.capacity !== undefined) apiPayload.capacity = data.capacity
  if (data.status !== undefined) apiPayload.status = data.status
  if (data.branchId !== undefined) apiPayload.branch_id = data.branchId
  return api.put<{ id: number; table_number: string; capacity: number; status: 'available' | 'reserved' | 'out_of_service'; branch_id: number }>(`/tables/${id}`, apiPayload)
    .then((r) => ({
      id: r.data.id,
      number: r.data.table_number,
      capacity: r.data.capacity,
      status: r.data.status,
      branchId: r.data.branch_id
    }))
}
export function deleteTable(id: number) {
  return api.delete(`/tables/${id}`)
}
