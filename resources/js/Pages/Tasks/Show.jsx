import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, task, project, userId, isAuthor, isContributor }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">{task.name}</h2>}
        >
            <Head title={task.name} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-start mb-6">
                                <div>
                                    <h3 className="text-2xl font-bold mb-2">{task.name}</h3>
                                    {task.description && (
                                        <p className="text-gray-600 mb-4">{task.description}</p>
                                    )}
                                    <div className="grid grid-cols-2 gap-4 text-sm text-gray-500">
                                        <div>
                                            <p><strong>Status:</strong> {task.status}</p>
                                            <p><strong>Importance:</strong> {task.importance}</p>
                                        </div>
                                        <div>
                                            <p><strong>Author:</strong> {task.author?.name}</p>
                                            {task.due_date && (
                                                <p><strong>Due Date:</strong> {new Date(task.due_date).toLocaleDateString()}</p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                                <div className="flex gap-2">
                                    <Link
                                        href={route('projects.show', project.id)}
                                        className="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Back to Project
                                    </Link>
                                    {(isAuthor || isContributor) && (
                                        <Link
                                            href={route('tasks.edit', task.id)}
                                            className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                        >
                                            Edit Task
                                        </Link>
                                    )}
                                </div>
                            </div>

                            {/* Comments Section */}
                            <div className="mt-8">
                                <div className="flex justify-between items-center mb-4">
                                    <h4 className="text-lg font-semibold">Comments</h4>
                                    <Link
                                        href={route('comments.create', { task_id: task.id })}
                                        className="bg-green-500 hover:bg-green-700 text-white text-sm py-1 px-3 rounded"
                                    >
                                        Add Comment
                                    </Link>
                                </div>
                                
                                {task.comments && task.comments.length > 0 ? (
                                    <div className="space-y-4">
                                        {task.comments.map((comment) => (
                                            <div key={comment.id} className="border rounded-lg p-4">
                                                <div className="flex justify-between items-start">
                                                    <div className="flex-1">
                                                        <p className="text-gray-800">{comment.name}</p>
                                                        <p className="text-xs text-gray-500 mt-1">
                                                            By {comment.author?.name} on {new Date(comment.created_at).toLocaleDateString()}
                                                        </p>
                                                    </div>
                                                    <div className="flex gap-2 ml-4">
                                                        <Link
                                                            href={route('comments.show', comment.id)}
                                                            className="bg-blue-500 hover:bg-blue-700 text-white text-xs py-1 px-2 rounded"
                                                        >
                                                            View
                                                        </Link>
                                                        {(project.author_id === userId || comment.author_id === userId) && (
                                                            <Link
                                                                href={route('comments.edit', comment.id)}
                                                                className="bg-yellow-500 hover:bg-yellow-700 text-white text-xs py-1 px-2 rounded"
                                                            >
                                                                Edit
                                                            </Link>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-gray-500">No comments yet. Be the first to comment!</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 