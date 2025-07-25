import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Link } from '@inertiajs/react';

export default function Index({ auth, projects, userId }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Projects</h2>}
        >
            <Head title="Projects" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-center mb-6">
                                <h3 className="text-lg font-semibold">My Projects</h3>
                                <Link
                                    href={route('projects.create')}
                                    className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Create New Project
                                </Link>
                            </div>

                            {projects.data.length === 0 ? (
                                <p className="text-gray-500">No projects found. Create your first project!</p>
                            ) : (
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    {projects.data.map((project) => (
                                        <div key={project.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                            <h4 className="font-semibold text-lg mb-2">{project.name}</h4>
                                            {project.description && (
                                                <p className="text-gray-600 text-sm mb-3">{project.description}</p>
                                            )}
                                            <div className="text-xs text-gray-500 mb-3">
                                                <p>Author: {project.author?.name}</p>
                                                <p>Contributors: {project.contributors?.length || 0}</p>
                                            </div>
                                            <div className="flex gap-2">
                                                <Link
                                                    href={route('projects.show', project.id)}
                                                    className="bg-green-500 hover:bg-green-700 text-white text-sm py-1 px-3 rounded"
                                                >
                                                    View
                                                </Link>
                                                {project.author_id === userId && (
                                                    <>
                                                        <Link
                                                            href={route('projects.edit', project.id)}
                                                            className="bg-blue-500 hover:bg-blue-700 text-white text-sm py-1 px-3 rounded"
                                                        >
                                                            Edit
                                                        </Link>
                                                        <Link
                                                            href={route('projects.contributors', project.id)}
                                                            className="bg-purple-500 hover:bg-purple-700 text-white text-sm py-1 px-3 rounded"
                                                        >
                                                            Contributors
                                                        </Link>
                                                    </>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {/* Pagination */}
                            {projects.links && projects.links.length > 3 && (
                                <div className="mt-6 flex justify-center">
                                    <nav className="flex space-x-2">
                                        {projects.links.map((link, index) => (
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