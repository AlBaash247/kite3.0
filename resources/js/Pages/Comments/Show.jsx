import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, comment, task, project, userId }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Comment Details</h2>}
        >
            <Head title="Comment Details" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="mb-6">
                                <Link
                                    href={route('tasks.show', task.id)}
                                    className="text-blue-500 hover:text-blue-700 text-sm"
                                >
                                    ← Back to Task
                                </Link>
                            </div>

                            <div className="border rounded-lg p-6">
                                <div className="mb-4">
                                    <h3 className="text-lg font-semibold mb-2">Comment</h3>
                                    <p className="text-gray-800 whitespace-pre-wrap">{comment.name}</p>
                                </div>

                                <div className="grid grid-cols-2 gap-4 text-sm text-gray-500 mb-4">
                                    <div>
                                        <p><strong>Author:</strong> {comment.author?.name}</p>
                                        <p><strong>Created:</strong> {new Date(comment.created_at).toLocaleDateString()}</p>
                                    </div>
                                    <div>
                                        <p><strong>Task:</strong> {task.name}</p>
                                        <p><strong>Project:</strong> {project.name}</p>
                                    </div>
                                </div>

                                <div className="flex gap-2">
                                    <Link
                                        href={route('tasks.show', task.id)}
                                        className="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Back to Task
                                    </Link>
                                    {(project.author_id === userId || comment.author_id === userId) && (
                                        <Link
                                            href={route('comments.edit', comment.id)}
                                            className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                        >
                                            Edit Comment
                                        </Link>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 