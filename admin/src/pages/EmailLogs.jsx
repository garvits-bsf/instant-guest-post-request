import React from 'react';

const data = [
  { id: 1, to: 'user@example.com', subject: 'New Submission', status: 'sent' },
];

const EmailLogs = () => (
  <div className="p-6">
    <table className="min-w-full border">
      <thead>
        <tr className="bg-gray-50">
          <th className="px-4 py-2 text-left">To</th>
          <th className="px-4 py-2 text-left">Subject</th>
          <th className="px-4 py-2 text-left">Status</th>
        </tr>
      </thead>
      <tbody>
        {data.map((row) => (
          <tr key={row.id} className="border-t">
            <td className="px-4 py-2">{row.to}</td>
            <td className="px-4 py-2">{row.subject}</td>
            <td className="px-4 py-2">{row.status}</td>
          </tr>
        ))}
      </tbody>
    </table>
  </div>
);

export default EmailLogs;
