import { createFileRoute } from '@tanstack/react-router';

import { DataTable } from '@/components/data-table/DataTable';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { TableCell, TableFooter, TableRow } from '@/components/ui/table';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { formatCurrency } from '@/lib/utils';
import { columns, Payment } from '@/pages/payment-table/columns';
import { PaymentCreateDialog } from '@/pages/payment-table/PaymentCreateDialog';
import { useQuery } from '@tanstack/react-query';
import { ColumnFiltersState, RowSelectionState, SortingState, Table, VisibilityState } from '@tanstack/react-table';
import dayjs from 'dayjs';
import qs from 'qs';
import { useState } from 'react';
export const Route = createFileRoute('/_private/dashboard/')({
  component: RouteComponent,
});

export function MonthSelector({ value, onValueChange }: { value: any[]; onValueChange: (value: any[]) => void }) {
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

  const handleValueChange = (value: string[]) => {
    // convert value to number
    const numbers = value.map((month) => months.indexOf(month) + 1);
    onValueChange(numbers);
  };

  const selectedMonths = value.map((month) => months[month - 1]);

  return (
    <ToggleGroup value={selectedMonths} type="multiple" variant="outline" onValueChange={handleValueChange}>
      {months.map((month) => (
        <ToggleGroupItem key={month} value={month} aria-label={month}>
          {month}
        </ToggleGroupItem>
      ))}
    </ToggleGroup>
  );
}

export function PaymentTypeSelector({ value, onValueChange, options }: { value: any[]; onValueChange: (value: any[]) => void; options: any[] }) {
  return (
    <ToggleGroup value={value} type="multiple" variant="outline" onValueChange={onValueChange}>
      {options?.map((option) => (
        <ToggleGroupItem key={option.id} value={option.id} aria-label={option.name}>
          {option.name}
        </ToggleGroupItem>
      ))}
    </ToggleGroup>
  );
}

const getPaymentData = async ({ sort, filter }: { sort: any; filter: any }) => {
  // convert to query string like ?filter[months]=1,2&filter[year]=2025
  // filter[months] to string with ',' separated
  const queryParams = qs.stringify({
    sort: sort,
    filter: {
      ...filter,
      months: filter.months.join(','),
    },
  });

  let url = `http://localhost:9999/api/payments?include=user,method,category.paymentType&${queryParams}`;

  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
    },
  });
  const data = await response.json();
  return data;
};

const getFilters = async () => {
  const response = await fetch('http://localhost:9999/api/payments-filters', {
    headers: {
      'Content-Type': 'application/json',
    },
  });
  const data = await response.json();
  return data;
};

type PaymentFilter = {
  months: number[];
  year: string;
  type: string[];
  description: string;
  category: string[];
  method: string[];
};

const PaymentFooter = ({ table, colspan }: { table: Table<Payment>; colspan: number }) => {
  const total = table.getRowModel().rows.map((row) => Number(row.original.amount));

  return (
    <TableFooter>
      <TableRow>
        <TableCell colSpan={colspan} className="text-right">
          Total: {formatCurrency(total.reduce((acc, amount) => acc + amount, 0))}
        </TableCell>
      </TableRow>
    </TableFooter>
  );
};

function RouteComponent() {
  const navigate = Route.useNavigate();
  const [sorting, setSorting] = useState<string>('');
  const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({});
  const [open, setOpen] = useState(false);
  const [filter, setFilter] = useState<PaymentFilter>({
    months: [dayjs().month() + 1],
    year: dayjs().year().toString(),
    type: [],
    description: '',
    category: [],
    method: [],
  });

  const { data, isPending, error } = useQuery({
    queryKey: ['payments', filter, sorting],
    queryFn: async () => {
      const data = await getPaymentData({ sort: sorting, filter });
      return data;
    },
  });

  const {
    data: filters,
    isPending: isFiltersPending,
    error: filtersError,
  } = useQuery({
    queryKey: ['filters'],
    queryFn: async () => {
      const data = await getFilters();
      return data.data;
    },
  });

  const handleMonthChange = (value: string[]) => {
    setFilter({ ...filter, months: value.map(Number) });
  };

  const handleYearChange = (value: string) => {
    setFilter({ ...filter, year: value });
  };

  const handlePaymentTypeChange = (value: string[]) => {
    setFilter({ ...filter, type: value });
  };

  const handleTableChange = (
    sorting: SortingState,
    columnVisibility: VisibilityState,
    rowSelection: RowSelectionState,
    columnFilters: ColumnFiltersState,
  ) => {
    // console.log(sorting, columnVisibility, rowSelection, columnFilters);
    // convert sorting to query string like ?sort=-payment_date
    const sortQuery = sorting.map((sort) => `${sort.desc ? '-' : ''}${sort.id}`).join(',');
    setSorting(sortQuery);
    setColumnVisibility(columnVisibility);

    setFilter({
      ...filter,
      // months: columnFilters.find(filter => filter.id === 'months')?.value as number[],
      // year: columnFilters.find(filter => filter.id === 'year')?.value as string,
      description: columnFilters.find((filter) => filter.id === 'description')?.value as string,
      category: columnFilters.find((filter) => filter.id === 'category')?.value as string[],
      method: columnFilters.find((filter) => filter.id === 'method')?.value as string[],
    });
  };

  const handlePaymentCreate = (data: any) => {
    console.log(data);
  };

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <div className="flex gap-2">
          <PaymentTypeSelector options={filters?.payment_types} onValueChange={handlePaymentTypeChange} value={filter.type} />
          <MonthSelector onValueChange={handleMonthChange} value={filter.months} />
          <Select value={filter.year} onValueChange={handleYearChange}>
            <SelectTrigger className="w-[180px]">
              <SelectValue placeholder="Year" />
            </SelectTrigger>
            <SelectContent>
              {filters?.years.map((year: any) => (
                <SelectItem key={year.year} value={year.year}>
                  {year.year}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
        <Button onClick={() => setOpen(true)}>Add Payment</Button>
      </div>
      <DataTable
        filters={filters}
        columns={columns}
        data={data?.data ?? []}
        onTableChange={handleTableChange}
        renderFooter={(table) => <PaymentFooter table={table as Table<Payment>} colspan={5} />}
        renderSubComponent={({ row }) => {
          return (
            <pre style={{ fontSize: '10px' }}>
              <code>{JSON.stringify(row.original, null, 2)}</code>
            </pre>
          );
        }}
      />
      <PaymentCreateDialog open={open} onOpenChange={setOpen} onSubmit={handlePaymentCreate} />
    </div>
  );
}
