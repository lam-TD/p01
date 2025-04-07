'use client';

import { CalendarIcon } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import dayjs from 'dayjs';

export function DatePicker({
  date,
  setDate,
  format = 'YYYY-MM-DD',
  className = '',
}: {
  date: Date;
  setDate: (date: Date) => void;
  format?: string;
  className?: string;
}) {
  return (
    <Popover>
      <PopoverTrigger asChild>
        <Button variant={'outline'} className={cn('w-[240px] justify-start text-left font-normal', className, !date && 'text-muted-foreground')}>
          <CalendarIcon />
          {date ? dayjs(date).format(format) : <span>Pick a date</span>}
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-auto p-0" align="start">
        <Calendar mode="single" selected={date} onSelect={(day) => setDate(day as Date)} initialFocus />
      </PopoverContent>
    </Popover>
  );
}
