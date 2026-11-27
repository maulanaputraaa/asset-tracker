'use client';

import { useEffect } from 'react';
import { useRouter, usePathname } from 'next/navigation';
import { useAuthStore } from '@/stores/authStore';
import { Sidebar } from '@/components/layout/Sidebar';
import { Topbar } from '@/components/layout/Topbar';
import { Skeleton } from '@/components/ui/skeleton';

export function AuthGuard({ children }: { children: React.ReactNode }) {
  const router = useRouter();
  const pathname = usePathname();
  const { isAuthenticated, isLoading, user } = useAuthStore();

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.replace('/login');
    }
  }, [isLoading, isAuthenticated, router]);

  // Children selalu di-render agar segment tidak terbuang (Next.js instant navigation).
  // Saat belum siap, overlay skeleton menutupi konten.
  const ready = !isLoading && isAuthenticated;

  return (
    <div className="relative flex h-screen w-full overflow-hidden bg-zinc-50 dark:bg-zinc-950">
      {!ready && (
        <div className="absolute inset-0 z-50 flex items-center justify-center bg-zinc-50 dark:bg-zinc-950">
          <div className="w-full max-w-sm space-y-4 p-6">
            <Skeleton className="mx-auto h-8 w-3/4" />
            <Skeleton className="mx-auto h-4 w-1/2" />
            <div className="space-y-2">
              <Skeleton className="h-10 w-full" />
              <Skeleton className="h-10 w-full" />
            </div>
          </div>
        </div>
      )}

      <Sidebar userRole={user?.role} currentPath={pathname} />
      <div className="flex flex-1 flex-col overflow-hidden">
        <Topbar user={user} />
        <main className="flex-1 overflow-y-auto p-6">{children}</main>
      </div>
    </div>
  );
}
