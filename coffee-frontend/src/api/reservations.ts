import { api } from './client'

export interface ReservationDTO {
  id: number
  userId: number
  tableId: number
  startAt: string
  status: 'pending' | 'confirmed' | 'canceled' | 'completed'
  notes?: string
}

interface ReservationRaw {
  id: number
  user_id: number
  table_id: number
  start_at: string
  res_status: 'pending' | 'confirmed' | 'canceled' | 'completed'
  res_notes?: string
}

export function getReservations(params?: { page?: number; per_page?: number }) {
  return api.get('/reservations', { params }).then((r) => {
    const data = r.data;
    if (data && typeof data === 'object' && 'data' in data) {
      // Paginated response
      return {
        ...data,
        data: data.data.map((reservation: ReservationRaw) => ({
          id: reservation.id,
          userId: reservation.user_id,
          tableId: reservation.table_id,
          startAt: reservation.start_at,
          status: reservation.res_status,
          notes: reservation.res_notes
        }))
      };
    } else {
      // Regular array response
      return data.map((reservation: ReservationRaw) => ({
        id: reservation.id,
        userId: reservation.user_id,
        tableId: reservation.table_id,
        startAt: reservation.start_at,
        status: reservation.res_status,
        notes: reservation.res_notes
      }));
    }
  });
}
export function getReservation(id: number) {
  return api.get<{ id: number; user_id: number; table_id: number; start_at: string; res_status: 'pending' | 'confirmed' | 'canceled' | 'completed'; res_notes?: string }>(`/reservations/${id}`)
    .then((r) => ({
      id: r.data.id,
      userId: r.data.user_id,
      tableId: r.data.table_id,
      startAt: r.data.start_at,
      status: r.data.res_status,
      notes: r.data.res_notes
    }))
}
export function createReservation(data: Pick<ReservationDTO, 'userId' | 'tableId' | 'startAt' | 'status' | 'notes'>) {
  const apiPayload = {
    user_id: data.userId,
    table_id: data.tableId,
    start_at: data.startAt,
    res_status: data.status,
    res_notes: data.notes
  }
  return api.post<{ id: number; user_id: number; table_id: number; start_at: string; res_status: 'pending' | 'confirmed' | 'canceled' | 'completed'; res_notes?: string }>('/reservations', apiPayload)
    .then((r) => ({
      id: r.data.id,
      userId: r.data.user_id,
      tableId: r.data.table_id,
      startAt: r.data.start_at,
      status: r.data.res_status,
      notes: r.data.res_notes
    }))
}
export function updateReservation(id: number, data: Partial<Pick<ReservationDTO, 'userId' | 'tableId' | 'startAt' | 'status' | 'notes'>>) {
  const apiPayload: Record<string, string | number | undefined> = {}
  if (data.userId !== undefined) apiPayload.user_id = data.userId
  if (data.tableId !== undefined) apiPayload.table_id = data.tableId
  if (data.startAt !== undefined) apiPayload.start_at = data.startAt
  if (data.status !== undefined) apiPayload.res_status = data.status
  if (data.notes !== undefined) apiPayload.res_notes = data.notes

  return api.put<{ id: number; user_id: number; table_id: number; start_at: string; res_status: 'pending' | 'confirmed' | 'canceled' | 'completed'; res_notes?: string }>(`/reservations/${id}`, apiPayload)
    .then((r) => ({
      id: r.data.id,
      userId: r.data.user_id,
      tableId: r.data.table_id,
      startAt: r.data.start_at,
      status: r.data.res_status,
      notes: r.data.res_notes
    }))
}
export function deleteReservation(id: number) {
  return api.delete(`/reservations/${id}`)
}
