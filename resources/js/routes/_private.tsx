import { AppSidebar } from '@/components/app-sidebar'
import { SidebarProvider } from '@/components/ui/sidebar'
import { SidebarInset } from '@/components/ui/sidebar'
import { createFileRoute, Outlet } from '@tanstack/react-router'
import { SiteHeader } from "@/components/site-header"

export const Route = createFileRoute('/_private')({
  component: RouteComponent,
})

function RouteComponent() {
  return <SidebarProvider
    style={
      {
        "--sidebar-width": "calc(var(--spacing) * 72)",
        "--header-height": "calc(var(--spacing) * 12)",
      } as React.CSSProperties
    }
  >
    <AppSidebar variant="inset" />
    <SidebarInset>
      <SiteHeader />
      <div className="flex flex-1 flex-col">
        <div className="@container/main flex flex-1 flex-col gap-2">
          <div className="flex flex-col gap-4 py-4 md:gap-6 md:py-6">
            <div className="mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 flex flex-col px-4 lg:px-6">
              <Outlet />
            </div>
          </div>
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
}
