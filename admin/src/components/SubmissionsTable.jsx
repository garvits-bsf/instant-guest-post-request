import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import {
  Button,
  Card,
  CardHeader,
  CardBody,
  CardFooter,
  Spinner,
  __experimentalHeading as Heading,
} from '@wordpress/components';

const SubmissionsTable = () => {
  const [submissions, setSubmissions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  useEffect(() => {
    fetchSubmissions();
  }, [page]);

  const fetchSubmissions = async () => {
    try {
      setLoading(true);
      const response = await fetch(`${igprData.apiUrl}/submissions?page=${page}`, {
        headers: {
          'X-WP-Nonce': igprData.nonce
        }
      });
      const data = await response.json();
      setSubmissions(data.submissions);
      setTotalPages(data.totalPages);
    } catch (error) {
      console.error('Error fetching submissions:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleApprove = async (id) => {
    if (confirm(__('Are you sure you want to approve this submission?'))) {
      try {
        const response = await fetch(`${igprData.apiUrl}/submissions/${id}/approve`, {
          method: 'POST',
          headers: {
            'X-WP-Nonce': igprData.nonce
          }
        });
        
        if (response.ok) {
          fetchSubmissions();
        }
      } catch (error) {
        console.error('Error approving submission:', error);
      }
    }
  };

  const handleReject = async (id) => {
    if (confirm(__('Are you sure you want to reject this submission?'))) {
      try {
        const response = await fetch(`${igprData.apiUrl}/submissions/${id}/reject`, {
          method: 'POST',
          headers: {
            'X-WP-Nonce': igprData.nonce
          }
        });
        
        if (response.ok) {
          fetchSubmissions();
        }
      } catch (error) {
        console.error('Error rejecting submission:', error);
      }
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center p-8">
        <Spinner />
      </div>
    );
  }

  return (
    <Card className="overflow-hidden">
      <CardHeader>
        <Heading level={3} className="text-lg font-medium text-gray-900">
          {__('Guest Post Submissions')}
        </Heading>
        <p className="mt-1 text-sm text-gray-500">
          {__('Manage guest post submissions')}
        </p>
      </CardHeader>

      <CardBody className="p-0">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{__('Title')}</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{__('Author')}</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{__('Date')}</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{__('Status')}</th>
              <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{__('Actions')}</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {submissions.length > 0 ? (
              submissions.map((submission) => (
                <tr key={submission.id}>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm font-medium text-gray-900">{submission.title}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm text-gray-900">{submission.author_name}</div>
                    <div className="text-sm text-gray-500">{submission.author_email}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm text-gray-500">{submission.date}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                      ${submission.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                        submission.status === 'published' ? 'bg-green-100 text-green-800' : 
                        'bg-red-100 text-red-800'}`}>
                      {submission.status}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div className="flex space-x-2">
                      <Button isLink href={submission.edit_url}>
                        {__('View')}
                      </Button>
                      {submission.status === 'pending' && (
                        <>
                          <Button
                            isSecondary
                            onClick={() => handleApprove(submission.id)}
                            className="text-green-700"
                          >
                            {__('Approve')}
                          </Button>
                          <Button
                            isSecondary
                            onClick={() => handleReject(submission.id)}
                            className="text-red-700"
                          >
                            {__('Reject')}
                          </Button>
                        </>
                      )}
                    </div>
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan="5" className="px-6 py-4 text-center text-sm text-gray-500">
                  {__('No submissions found')}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
      </CardBody>

      {totalPages > 1 && (
        <CardFooter className="flex items-center justify-between">
          <div className="flex-1 flex justify-between">
            <Button
              onClick={() => setPage(Math.max(1, page - 1))}
              disabled={page === 1}
            >
              {__('Previous')}
            </Button>
            <Button
              onClick={() => setPage(Math.min(totalPages, page + 1))}
              disabled={page === totalPages}
            >
              {__('Next')}
            </Button>
          </div>
        </CardFooter>
      )}
    </Card>
  );
};

export default SubmissionsTable;