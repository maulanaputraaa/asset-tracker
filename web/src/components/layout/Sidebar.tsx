'use client';

import Link from 'next/link';
import { useState } from 'react';
import { cn } from 'cn';
import {
  LayoutDashboard,
  Box,
  Warehouse,
  Tags,
  MapPin,
  Building2,
  Truck,
  Send,
  Wrench,
  ClipboardList,
  CheckCircle,
  Menu,
  X,
} from 'lucide-react';
import type { UserRole } from '@/types';

type NavItem = {
  label: string;
  href: string;
  icon: React.ReactNode;
  roles?: UserRole[];
};

const navGroups: { label: string; items: NavItem[] }[] = [
  {
    label: 'Utama',
    items: [
      { label: 'Dashboard', href: '/dashboard', icon: <LayoutDashboard className="size-4" /> },
      { label: 'Aset', href: '/assets', icon: <Box className="size-4" /> },
    ],
  },
  {
    label: 'Master Data',
    items: [
      { label: 'Site / Gudang', href: '/sites', icon: <Warehouse className="size-4" /> },
      { label: 'Kategori', href: '/categories', icon: <Tags className="size-4" /> },
      { label: 'Lokasi', href: '/locations', icon: <MapPin className="size-4" /> },
      { label: 'Departemen', href: '/departments', icon: <Building2 className="size-4" /> },
      { label: 'Vendor', href: '/vendors', icon: <Truck className="size-4" /> },
    ],
  },
  {
    label: 'Operasional',
    items: [
      { label: 'Transfer', href: '/transfers', icon: <Send className="size-4" /> },
      { label: 'Work Order', href: '/work-orders', icon: <Wrench className="size-4" /> },
      { label: 'Stock Opname', href: '/stock-opnames', icon: <ClipboardList className="size-4" /> },
      { label: 'Approval', href: '/approvals', icon: <CheckCircle className="size-4" /> },
    ],
  },
];

function Sidebar({
  userRole,
  currentPath,
}: {
  userRole?: UserRole;
  currentPath: string;
}) {
  const [mobileOpen, setMobileOpen] = useState(false);

  const filtered = navGroups.map((g) => ({
    ...g,
    items: g.items.filter(
      (item) => !item.roles || (userRole && item.roles.includes(userRole))
    ),
  }));

  return (
    <>
      {/* Overlay mobile */}
      {mobileOpen && (
        <div
          className="fixed inset-0 z-40 bg-black/50 md:hidden"
          onClick={() => setMobileOpen(false)}
        />
      )}

      {/* Toggle mobile */}
      <button
        className="fixed top-4 left-4 z-50 md:hidden rounded-lg border bg-background p-2 shadow-sm"
        onClick={() => setMobileOpen((v) => !v)}
      >
        {mobileOpen ? <X className="size-4" /> : <Menu className="size-4" />}
      </button>

      <aside
        className={cn(
          'fixed inset-y-0 left-0 z-40 w-64 transform border-r bg-background transition-transform duration-200 md:relative md:translate-x-0',
          mobileOpen ? 'translate-x-0' : '-translate-x-full'
        )}
      >
        <div className="flex h-16 items-center border-b px-6">
          <Link href="/dashboard" className="flex items-center gap-2 font-semibold">
            <Box className="size-5 text-primary" />
            <span>Asset Tracker</span>
          </Link>
        </div>

        <nav className="flex flex-col gap-6 p-4">
          {filtered.map((group) => (
            <div key={group.label} className="flex flex-col gap-1">
              <span className="px-3 text-xs font-medium text-muted-foreground">
                {group.label}
              </span>
              {group.items.map((item) => {
                const isActive =
                  currentPath === item.href ||
                  currentPath.startsWith(item.href + '/');
                return (
                  <Link
                    key={item.href}
                    href={item.href}
                    onClick={() => setMobileOpen(false)}
                    className={cn(
                      'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
                      isActive
                        ? 'bg-primary text-primary-foreground'
                        : 'text-foreground hover:bg-muted'
                    )}
                  >
                    {item.icon}
                    {item.label}
                  </Link>
                );
              })}
            </div>
          ))}
        </nav>
      </aside>
    </>
  );
}

export { Sidebar };
