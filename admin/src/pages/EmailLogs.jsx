import React from 'react';
import { Table } from '@brainstormforce/force-ui';

const data = [
  { id: 1, to: 'user@example.com', subject: 'New Submission', status: 'sent' },
];

const EmailLogs = () => {
  const columns = [
    { Header: 'To', accessor: 'to' },
    { Header: 'Subject', accessor: 'subject' },
    { Header: 'Status', accessor: 'status' },
  ];

  return (
    <div className="p-6">
      <Table columns={columns} data={data} />
    </div>
  );
};

export default EmailLogs;
