import { createFileRoute } from '@tanstack/react-router'
import { Bold, Italic, Underline } from "lucide-react"

import {
  ToggleGroup,
  ToggleGroupItem,
} from "@/components/ui/toggle-group"
import { PaymentTable } from '@/pages/payment-table/PaymentTable'
import qs from 'qs';
import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import dayjs from 'dayjs';

export const Route = createFileRoute('/_private/dashboard/')({
  component: RouteComponent,
});


export function MonthSelector({ value, onValueChange }: { value: any[], onValueChange: (value: any[]) => void }) {
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

  const handleValueChange = (value: string[]) => {
    // convert value to number
    const numbers = value.map(month => months.indexOf(month) + 1);
    onValueChange(numbers);
  }

  const selectedMonths = value.map(month => months[month - 1]);


  return (
    <ToggleGroup value={selectedMonths} type="multiple" variant="outline" onValueChange={handleValueChange}>
      {months.map((month) => (
        <ToggleGroupItem key={month} value={month} aria-label={month}>
          {month}
        </ToggleGroupItem>
      ))}
    </ToggleGroup>
  )
}

const getPaymentData = async ({ filter }: { filter: any }) => {
  // convert to query string like ?filter[months]=1,2&filter[year]=2025
  // filter[months] to string with '-' separated
  const queryParams = qs.stringify({
    filter: {
      ...filter,
      months: filter.months.join('-'),
    }
  });

  let url = `http://localhost:9999/api/payments?include=user,method,category&${queryParams}`;


  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
    },
  })
  const data = await response.json()
  return data;
}

const getFilters = async () => {
  const response = await fetch('http://localhost:9999/api/payments-filters', {
    headers: {
      'Content-Type': 'application/json',
    },
  });
  const data = await response.json()
  return data;
}


function RouteComponent() {
  const navigate = Route.useNavigate();
  const [filter, setFilter] = useState({
    months: [dayjs().month() + 1],
    year: dayjs().year().toString(),

  })

  const { data, isPending, error } = useQuery({
    queryKey: ['payments', filter],
    queryFn: async () => {
      const data = await getPaymentData({filter});
      return data;
    },
  });

  const { data: filters, isPending: isFiltersPending, error: filtersError } = useQuery({
    queryKey: ['filters'],
    queryFn: async () => {
      const data = await getFilters();
      return data.data;
    },
  });

  const handleMonthChange = (value: string[]) => {
    setFilter({...filter, months: value.map(Number)});
  }

  const handleYearChange = (value: string) => {
    setFilter({ ...filter, year: (value) });
  }

  return <div className='flex flex-col gap-4'>
    
    <div className="flex justify-between items-center ">
      <div className="flex gap-2">
        <MonthSelector onValueChange={handleMonthChange} value={filter.months} />
        <Select value={filter.year} onValueChange={handleYearChange}>
          <SelectTrigger className="w-[180px]">
            <SelectValue placeholder="Year" />
          </SelectTrigger>
          <SelectContent>
            {filters?.years.map((year: any) => (
              <SelectItem key={year.year} value={year.year}>{year.year}</SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>
      <Button>Add Payment</Button>
      
    </div>
    <PaymentTable isPending={isPending} data={data} />
  </div>
}
