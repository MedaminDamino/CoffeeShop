import { api } from './client'

export interface ReservationDTO {
  id: number
  userId: number
  tableId: number
  startAt: string
  endAt?: string
  status: 'pending' | 'confirmed' | 'canceled' | 'completed'
  notes?: string
}

export function getReservations() {
  return api.get<{ id: number; user_id: number; table_id: number; start_at: string; end_at?: string; res_status: 'pending' | 'confirmed' | 'canceled' | 'completed'; res_notes?: string }[]>('/reservations')
    .then((r) => r.data.map(res => ({
      id: res.id,
      userId: res.user_id,
      tableId: res.table_id,
      startAt: res.start_at,
      endAt: res.end_at,
      status: res.res_status,
      notes: res.res_notes
    })))
}
export function getReservation(id: number) {
  return api.get<{ id: number; user_id: number; table_id: number; start_at: string; end_at?: string; res_status: 'pending' | 'confirmed' | 'canceled' | 'completed'; res_notes?: string }>(`/reservations/${id}`)
    .then((r) => ({
      id: r.data.id,
      userId: r.data.user_id,
      tableId: r.data.table_id,
      startAt: r.data.start_at,
      endAt: r.data.end_at,
      status: r.data.res_status,
      notes: r.data.res_notes
    }))
}
export function createReservation(data: Pick<ReservationDTO, 'userId' | 'tableId' | 'startAt' | 'endAt' | 'status' | 'notes'>) {
  const apiPayload = {
    user_id: data.userId,
    table_id: data.tableId,
    start_at: data.startAt,
    end_at: data.endAt,
    res_status: data.status,
    res_notes: data.notes
  }
  return api.post<{ id: number; user_id: number; table_id: number; start_at: string; end_at?: string; res_status: 'pending' | 'confirmed' | 'canceled' | 'completed'; res_notes?: string }>('/reservations', apiPayload)
    .then((r) => ({
      id: r.data.id,
      userId: r.data.user_id,
      tableId: r.data.table_id,
      startAt: r.data.start_at,
      endAt: r.data.end_at,
      status: r.data.res_status,
      notes: r.data.res_notes
    }))
}
export function deleteReservation(id: number) {
  return api.delete(`/reservations/${id}`)
}
