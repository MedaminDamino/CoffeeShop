import { api } from './client'

export interface User {
  id: number
  name: string
  username: string
  email: string
  birthday?: string
  role: string
}

export interface AuthResponse {
  token: string
  user: User
}

export function register(payload: { username: string; email: string; birthday: string; password: string; password_confirmation: string }) {
  return api.post<AuthResponse>('/register', payload).then((r) => r.data)
}

export function login(payload: { username: string; password: string }) {
  return api.post<AuthResponse>('/login', payload).then((r) => r.data)
}

export function forgotPassword(payload: { email: string }) {
  return api.post<{ message: string }>('/forgot-password', payload).then((r) => r.data)
}

export function getUser() {
  return api.get<User>('/user').then((r) => r.data)
}
