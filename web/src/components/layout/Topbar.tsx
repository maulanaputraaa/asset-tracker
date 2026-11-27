'use client';

import { LogOut, UserCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { useAuthStore } from '@/stores/authStore';
import type { User } from '@/types';

function Topbar({ user }: { user: User | null }) {
  const logout = useAuthStore((s) => s.logout);

  return (
    <header className="flex h-16 items-center justify-between border-b bg-background px-6">
      <h2 className="text-lg font-semibold">Dashboard</h2>

      <div className="flex items-center gap-4">
        <div className="flex items-center gap-2">
          <UserCircle className="size-5 text-muted-foreground" />
          <div className="hidden flex-col items-end sm:flex">
            <span className="text-sm font-medium">{user?.name ?? 'Guest'}</span>
            <Badge variant="secondary" className="text-[10px] capitalize">
              {user?.role ?? '-'}
            </Badge>
          </div>
        </div>

        <Button variant="ghost" size="icon" onClick={logout} title="Logout">
          <LogOut className="size-4" />
        </Button>
      </div>
    </header>
  );
}

export { Topbar };
