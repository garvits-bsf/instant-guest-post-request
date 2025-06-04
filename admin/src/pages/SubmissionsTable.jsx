import React from 'react';
import { Table, Badge, DropdownMenu, Button } from '@brainstormforce/force-ui';

const data = [
  { id: 1, title: 'Post 1', status: 'pending' },
  { id: 2, title: 'Post 2', status: 'approved' },
];

const SubmissionsTable = () => {
  const columns = [
    { Header: 'Title', accessor: 'title' },
    {
      Header: 'Status',
      accessor: 'status',
      Cell: ({ value }) => (
        <Badge variant={value === 'approved' ? 'success' : 'warning'}>
          {value}
        </Badge>
      ),
    },
    {
      Header: 'Actions',
      accessor: 'id',
      Cell: ({ value }) => (
        <DropdownMenu
          label="Actions"
          items={[
            { label: 'Approve', onClick: () => console.log('approve', value) },
            { label: 'Reject', onClick: () => console.log('reject', value) },
          ]}
        />
      ),
    },
  ];

  return (
    <div className="p-6">
      <div className="mb-4">
        <Button variant="primary" onClick={() => console.log('bulk approve')}>
          Bulk Approve
        </Button>
        <Button
          className="ml-2"
          variant="destructive"
          onClick={() => console.log('bulk reject')}
        >
          Bulk Reject
        </Button>
      </div>
      <Table columns={columns} data={data} />
    </div>
  );
};

export default SubmissionsTable;
