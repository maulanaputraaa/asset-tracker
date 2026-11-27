// Role sesuai PRD FR-1.4
export type UserRole = 'admin' | 'teknisi' | 'karyawan';

// Representasi user dari backend
export interface User {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    tenant_id: number;
    site_id?: number;           // site yang ditugaskan
    phone?: string;
    is_active: boolean;
    notification_preferences?: {
        email: boolean;
        push: boolean;
    };
    last_login_at?: string;
    created_at: string;
    updated_at: string;
}

// DTO untuk register/create user
export type CreateUserDto = Pick<User, 'name' | 'email' | 'role' | 'site_id' | 'phone'> & {
    password: string;
    password_confirmation: string;  // konfirmasi password
};

// DTO untuk login (hanya email + password)
export type LoginDto = Pick<User, 'email'> & {
    password: string;
};
