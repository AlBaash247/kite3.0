import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Show({ auth, project, userId, isAuthor, isContributor }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">{project.name}</h2>}
        >
            <Head title={project.name} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-start mb-6">
                                <div>
                                    <h3 className="text-2xl font-bold mb-2">{project.name}</h3>
                                    {project.description && (
                                        <p className="text-gray-600 mb-4">{project.description}</p>
                                    )}
                                    <div className="text-sm text-gray-500">
                                        <p>Author: {project.author?.name}</p>
                                        <p>Created: {new Date(project.created_at).toLocaleDateString()}</p>
                                    </div>
                                </div>
                                <div className="flex gap-2">
                                    {(isAuthor || isContributor) && (
                                        <Link
                                            href={route('tasks.create', { project_id: project.id })}
                                            className="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                        >
                                            Add Task
                                        </Link>
                                    )}
                                    {isAuthor && (
                                        <>
                                            <Link
                                                href={route('projects.edit', project.id)}
                                                className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                            >
                                                Edit Project
                                            </Link>
                                            <Link
                                                href={route('projects.contributors', project.id)}
                                                className="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded"
                                            >
                                                Manage Contributors
                                            </Link>
                                        </>
                                    )}
                                </div>
                            </div>

                            {/* Tasks Section */}
                            <div className="mt-8">
                                <h4 className="text-lg font-semibold mb-4">Tasks</h4>
                                {project.tasks && project.tasks.length > 0 ? (
                                    <div className="grid gap-4">
                                        {project.tasks.map((task) => (
                                            <div key={task.id} className="border rounded-lg p-4">
                                                <div className="flex justify-between items-start">
                                                    <div>
                                                        <h5 className="font-semibold">{task.name}</h5>
                                                        {task.description && (
                                                            <p className="text-gray-600 text-sm mt-1">{task.description}</p>
                                                        )}
                                                        <div className="flex gap-4 mt-2 text-xs text-gray-500">
                                                            <span>Status: {task.status}</span>
                                                            <span>Importance: {task.importance}</span>
                                                            {task.due_date && (
                                                                <span>Due: {new Date(task.due_date).toLocaleDateString()}</span>
                                                            )}
                                                        </div>
                                                    </div>
                                                    <Link
                                                        href={route('tasks.show', task.id)}
                                                        className="bg-blue-500 hover:bg-blue-700 text-white text-sm py-1 px-3 rounded"
                                                    >
                                                        View
                                                    </Link>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-gray-500">No tasks yet. Create your first task!</p>
                                )}
                            </div>

                            {/* Contributors Section */}
                            <div className="mt-8">
                                <h4 className="text-lg font-semibold mb-4">Contributors</h4>
                                {project.contributors && project.contributors.length > 0 ? (
                                    <div className="grid gap-2">
                                        {project.contributors.map((contributor) => (
                                            <div key={contributor.id} className="flex justify-between items-center p-2 bg-gray-50 rounded">
                                                <div>
                                                    <span className="font-medium">{contributor.user?.name}</span>
                                                    <span className="text-sm text-gray-500 ml-2">
                                                        {contributor.is_editor ? '(Editor)' : '(Viewer)'}
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-gray-500">No contributors yet.</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 