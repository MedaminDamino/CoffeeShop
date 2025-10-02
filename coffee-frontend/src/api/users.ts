import { api } from './client'

export interface User {
  id: number
  name: string
  username: string
  email: string
  role: string
  birthday?: string
  created_at: string
}

export function getUsers() {
  return api.get<User[]>('/users').then((r) => r.data)
}

export function updateUserRole(userId: number, role: string) {
  return api.put<User>(`/users/${userId}/role`, { role }).then((r) => r.data)
}