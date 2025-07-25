import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ auth, tasks, project, userId, isAuthor, isContributor }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Tasks - {project.name}</h2>}
        >
            <Head title="Tasks" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-center mb-6">
                                <div>
                                    <h3 className="text-lg font-semibold">Tasks for {project.name}</h3>
                                    <Link
                                        href={route('projects.show', project.id)}
                                        className="text-blue-500 hover:text-blue-700 text-sm"
                                    >
                                        ← Back to Project
                                    </Link>
                                </div>
                                {(isAuthor || isContributor) && (
                                    <Link
                                        href={route('tasks.create', { project_id: project.id })}
                                        className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Create New Task
                                    </Link>
                                )}
                            </div>

                            {tasks.data.length === 0 ? (
                                <p className="text-gray-500">No tasks found. Create your first task!</p>
                            ) : (
                                <div className="grid gap-4">
                                    {tasks.data.map((task) => (
                                        <div key={task.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                            <div className="flex justify-between items-start">
                                                <div className="flex-1">
                                                    <h4 className="font-semibold text-lg mb-2">{task.name}</h4>
                                                    {task.description && (
                                                        <p className="text-gray-600 text-sm mb-3">{task.description}</p>
                                                    )}
                                                    <div className="flex gap-4 text-xs text-gray-500">
                                                        <span>Status: {task.status}</span>
                                                        <span>Importance: {task.importance}</span>
                                                        {task.due_date && (
                                                            <span>Due: {new Date(task.due_date).toLocaleDateString()}</span>
                                                        )}
                                                        <span>Author: {task.author?.name}</span>
                                                    </div>
                                                </div>
                                                <div className="flex gap-2 ml-4">
                                                    <Link
                                                        href={route('tasks.show', task.id)}
                                                        className="bg-green-500 hover:bg-green-700 text-white text-sm py-1 px-3 rounded"
                                                    >
                                                        View
                                                    </Link>
                                                    {(isAuthor || isContributor) && (
                                                        <Link
                                                            href={route('tasks.edit', task.id)}
                                                            className="bg-blue-500 hover:bg-blue-700 text-white text-sm py-1 px-3 rounded"
                                                        >
                                                            Edit
                                                        </Link>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {/* Pagination */}
                            {tasks.links && tasks.links.length > 3 && (
                                <div className="mt-6 flex justify-center">
                                    <nav className="flex space-x-2">
                                        {tasks.links.map((link, index) => (
                                            <Link
                                                key={index}
                                                href={link.url}
                                                className={`px-3 py-2 text-sm rounded ${
                                                    link.active
                                                        ? 'bg-blue-500 text-white'
                                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                                } ${!link.url && 'opacity-50 cursor-not-allowed'}`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ))}
                                    </nav>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 