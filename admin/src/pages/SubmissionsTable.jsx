import React from 'react';

const data = [
  { id: 1, title: 'Post 1', status: 'pending' },
  { id: 2, title: 'Post 2', status: 'approved' },
];

const SubmissionsTable = () => (
  <div className="p-6">
    <div className="mb-4">
      <button
        className="bg-blue-600 text-white px-3 py-2 rounded"
        onClick={() => console.log('bulk approve')}
      >
        Bulk Approve
      </button>
      <button
        className="bg-red-600 text-white px-3 py-2 rounded ml-2"
        onClick={() => console.log('bulk reject')}
      >
        Bulk Reject
      </button>
    </div>
    <table className="min-w-full border">
      <thead>
        <tr className="bg-gray-50">
          <th className="px-4 py-2 text-left">Title</th>
          <th className="px-4 py-2 text-left">Status</th>
          <th className="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        {data.map((row) => (
          <tr key={row.id} className="border-t">
            <td className="px-4 py-2">{row.title}</td>
            <td className="px-4 py-2">
              <span
                className={`px-2 py-1 rounded text-xs ${
                  row.status === 'approved'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-yellow-100 text-yellow-800'
                }`}
              >
                {row.status}
              </span>
            </td>
            <td className="px-4 py-2">
              <button
                className="text-blue-600 hover:underline mr-2"
                onClick={() => console.log('approve', row.id)}
              >
                Approve
              </button>
              <button
                className="text-red-600 hover:underline"
                onClick={() => console.log('reject', row.id)}
              >
                Reject
              </button>
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  </div>
);

export default SubmissionsTable;
