'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { useForm } from 'react-hook-form';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import api from '@/lib/api';
import type { LoginDto, User } from '@/types';
import { useAuthStore } from '@/stores/authStore';

export default function LoginPage() {
    const router = useRouter();
    const login = useAuthStore((s) => s.login);
    const [error, setError] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);

    const { register, handleSubmit, formState: { errors } } = useForm<LoginDto>({
        defaultValues: { email: '', password: '' },
    });

    const onSubmit = async (data: LoginDto) => {
        setError(null);
        setLoading(true);
        try {
            const res = await api.post('/login', data);
            const { user, access_token } = res.data;
            login(user as User, access_token);
            router.replace('/dashboard');
        } catch (err: unknown) {
            const e = err as { response?: { data?: { message?: string } } };
            setError(e.response?.data?.message ?? 'Login gagal. Coba lagi.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="flex min-h-screen items-center justify-center bg-zinc-50 dark:bg-zinc-950">
            <div className="w-full max-w-sm space-y-6 p-6">
                <div className="space-y-2 text-center">
                    <h1 className="text-2xl font-semibold tracking-tight">Asset Tracker</h1>
                    <p className="text-sm text-muted-foreground">Masuk ke akun kamu</p>
                </div>

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            placeholder="admin@demo.com"
                            {...register('email', {
                                required: 'Email wajib diisi',
                                pattern: { value: /\S+@\S+\.\S+/, message: 'Format email salah' },
                            })}
                        />
                        {errors.email && (
                            <p className="text-xs text-destructive">{errors.email.message}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            placeholder="••••••••"
                            {...register('password', { required: 'Password wajib diisi' })}
                        />
                        {errors.password && (
                            <p className="text-xs text-destructive">{errors.password.message}</p>
                        )}
                    </div>

                    {error && (
                        <p className="text-sm text-destructive text-center">{error}</p>
                    )}

                    <Button type="submit" className="w-full" disabled={loading}>
                        {loading ? 'Memproses...' : 'Masuk'}
                    </Button>
                </form>

                <p className="text-xs text-center text-muted-foreground">
                    Demo: admin@demo.com / password
                </p>
            </div>
        </div>
    );
}
