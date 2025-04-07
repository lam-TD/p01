import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function formatCurrency(amount: number, currency: string = 'VND') {
  const localeMap = {
    VND: 'vi-VN',
    USD: 'en-US',
  };

  return new Intl.NumberFormat(localeMap[currency as keyof typeof localeMap], { style: 'currency', currency }).format(amount);
}
