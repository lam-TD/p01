import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCaption, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import dayjs from 'dayjs';

const formatAmountWithoutCurrency = (amount: number) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(amount);
};

const humanizeDate = (date: string) => {
  return dayjs(date).format('DD/MM/YYYY');
};

export function PaymentTable({ isPending, data }: { isPending: boolean; data: any }) {
  if (isPending) return <div>Loading...</div>;

  const totalAmount = data.data.reduce((acc: number, payment: any) => acc + Number(payment.amount), 0);

  if (!data?.data) return <div>No data</div>;

  return (
    <Table>
      <TableCaption>A list of your recent invoices.</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead className="">Date time</TableHead>
          <TableHead>Payment Type</TableHead>
          <TableHead>Category</TableHead>
          <TableHead>Method</TableHead>
          <TableHead className="text-right">Amount</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {data.data.map((payment: any) => (
          <TableRow key={payment.id}>
            <TableCell className="font-medium">{humanizeDate(payment.payment_date)}</TableCell>
            <TableCell>
              <Badge variant="outline" style={{ color: payment.category.payment_type.color }}>
                {payment.category.payment_type.name}
              </Badge>
            </TableCell>
            <TableCell>
              <Badge variant="outline" style={{ color: payment.category.color }}>
                {payment.category.name}
              </Badge>
            </TableCell>
            <TableCell>{payment.method.name}</TableCell>
            <TableCell className="text-right">{formatAmountWithoutCurrency(payment.amount)}</TableCell>
          </TableRow>
        ))}
      </TableBody>
      <TableFooter>
        <TableRow>
          <TableCell colSpan={3}>Total</TableCell>
          <TableCell className="text-right">{formatAmountWithoutCurrency(totalAmount)}</TableCell>
        </TableRow>
      </TableFooter>
    </Table>
  );
}
