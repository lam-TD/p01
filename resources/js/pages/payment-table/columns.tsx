'use client';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { cn, formatCurrency } from '@/lib/utils';
import { ColumnDef, Row } from '@tanstack/react-table';
import dayjs from 'dayjs';
import { CircleMinus, CirclePlus, Pencil, Trash } from 'lucide-react';
import { DataTableColumnHeader } from '../../components/data-table/DataTableColumnHeader';

export type Category = {
  name: string;
  color: string;
  payment_type: {
    id: string;
    name: string;
  };
};

// This type is used to define the shape of our data.
// You can use a Zod schema here if you want.
export type Payment = {
  id: string;
  payment_date: string;
  category: Category;
  method: {
    name: string;
  };
  amount: number;
};

const CategoryCell = ({ row }: { row: Row<Payment> }) => {
  const category = row.getValue('category') as Category;
  return (
    <Badge variant="outline" style={{ color: category.color }}>
      {category.name}
    </Badge>
  );
};

const AmountCell = ({ row }: { row: Row<Payment> }) => {
  const amount = row.getValue('amount') as number;
  const category = row.getValue('category') as Category;
  const type = category.payment_type.name;
  const formatted = formatCurrency(amount, 'VND');

  const formattedAmount = type === 'Chi tiêu' ? `-${formatted}` : formatted;

  return <div className={cn('text-right', type === 'Thu nhập' && 'text-green-500')}>{formattedAmount}</div>;
};

export const columns: ColumnDef<Payment>[] = [
  {
    accessorKey: 'expander',
    header: () => null,
    cell: ({ row }) => {
      return row.getCanExpand() ? (
        <Button
          variant="ghost"
          size="icon"
          {...{
            onClick: row.getToggleExpandedHandler(),
            style: { cursor: 'pointer' },
          }}
        >
          {row.getIsExpanded() ? <CircleMinus /> : <CirclePlus />}
        </Button>
      ) : (
        <div className="w-4"></div>
      );
    },
  },
  {
    accessorKey: 'payment_date',
    enableHiding: false,
    enableGrouping: true,

    header: ({ column }) => <DataTableColumnHeader column={column} title="Date" className="justify-end" />,
    cell: ({ row }) => {
      const date = dayjs(row.getValue('payment_date'));
      return <div className="text-right font-medium">{date.format('DD/MM/YYYY')}</div>;
    },
  },
  {
    accessorKey: 'category',
    header: 'Category',
    cell: CategoryCell,
  },
  {
    accessorKey: 'method',
    header: 'Method',
    cell: ({ row }) => {
      const method = row.getValue('method') as { name: string };
      return <div className="">{method.name}</div>;
    },
  },
  {
    accessorKey: 'amount',
    enableHiding: false,
    header: ({ column }) => <DataTableColumnHeader column={column} title="Amount" className="justify-end" />,
    cell: AmountCell,
  },
  {
    accessorKey: 'description',
    header: 'Description',
    cell: ({ row }) => {
      const description = row.getValue('description') as string;
      return (
        <div className="truncate" title={description}>
          {description}
        </div>
      );
    },
  },
  {
    accessorKey: 'actions',
    header: () => <div className="w-4 text-center"></div>,
    cell: ({ row }) => {
      return (
        <div className="flex justify-center gap-2">
          <Button size="sm" variant="ghost">
            <Pencil /> Edit
          </Button>
          <Button size="sm" variant="ghost" className="text-red-500">
            <Trash /> Delete
          </Button>
        </div>
      );
    },
  },
];
