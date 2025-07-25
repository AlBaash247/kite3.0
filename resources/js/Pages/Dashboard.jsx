import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h3 className="text-lg font-semibold mb-4">Welcome to your Dashboard!</h3>
                            <p className="mb-4">Manage your projects, tasks, and collaborate with your team.</p>
                            <div className="flex gap-4">
                                <Link
                                    href={route('projects.index')}
                                    className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    View Projects
                                </Link>
                                <Link
                                    href={route('projects.create')}
                                    className="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Create New Project
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
