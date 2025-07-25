import { Head, useForm, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

export default function Contributors({ auth, project, contributors }) {
    const { data, setData, post, put, processing, errors } = useForm({
        email: '',
        is_editor: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('projects.contributors.add', project.id));
    };

    const updateContributor = (contributorId, isEditor) => {
        put(route('projects.contributors.update', { project_id: project.id, contributor_id: contributorId }), {
            data: { is_editor: isEditor }
        });
    };

    const removeContributor = (contributorId) => {
        console.log("contributorId", contributorId);
        if (confirm('Are you sure you want to remove this contributor?')) {
            router.delete(route('projects.contributors.remove', { project_id: project.id, contributor_id: contributorId }));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Manage Contributors</h2>}
        >
            <Head title="Manage Contributors" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="mb-6">
                                <h3 className="text-lg font-semibold mb-4">Project: {project.name}</h3>
                                <Link
                                    href={route('projects.show', project.id)}
                                    className="text-blue-500 hover:text-blue-700"
                                >
                                    ← Back to Project
                                </Link>
                            </div>

                            {/* Add Contributor Form */}
                            <div className="mb-8">
                                <h4 className="text-md font-semibold mb-4">Add New Contributor</h4>
                                <form onSubmit={submit} className="max-w-md">
                                    <div className="mb-4">
                                        <InputLabel htmlFor="email" value="Email Address" />
                                        <TextInput
                                            id="email"
                                            type="email"
                                            name="email"
                                            value={data.email}
                                            className="mt-1 block w-full"
                                            onChange={(e) => setData('email', e.target.value)}
                                            required
                                        />
                                        <InputError message={errors.email} className="mt-2" />
                                    </div>

                                    <div className="mb-4">
                                        <label className="flex items-center">
                                            <input
                                                type="checkbox"
                                                name="is_editor"
                                                checked={data.is_editor}
                                                onChange={(e) => setData('is_editor', e.target.checked)}
                                                className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            />
                                            <span className="ml-2 text-sm text-gray-600">Grant editor permissions</span>
                                        </label>
                                    </div>

                                    <div className="flex items-center justify-end mt-4">
                                        <PrimaryButton className="ml-4" disabled={processing}>
                                            Add Contributor
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>

                            {/* Current Contributors */}
                            <div>
                                <h4 className="text-md font-semibold mb-4">Current Contributors</h4>
                                {contributors && contributors.length > 0 ? (
                                    <div className="space-y-4">
                                        {contributors.map((contributor) => (
                                            <div key={contributor.id} className="flex justify-between items-center p-4 border rounded-lg">
                                                <div>
                                                    <span className="font-medium">{contributor.user?.name}</span>
                                                    <span className="text-sm text-gray-500 ml-2">({contributor.user?.email})</span>
                                                    <span className={`ml-2 px-2 py-1 text-xs rounded ${
                                                        contributor.is_editor 
                                                            ? 'bg-blue-100 text-blue-800' 
                                                            : 'bg-gray-100 text-gray-800'
                                                    }`}>
                                                        {contributor.is_editor ? 'Editor' : 'Viewer'}
                                                    </span>
                                                </div>
                                                <div className="flex gap-2">
                                                    <button
                                                        onClick={() => updateContributor(contributor.contributor_id, !contributor.is_editor)}
                                                        className="bg-yellow-500 hover:bg-yellow-700 text-white text-sm py-1 px-3 rounded"
                                                    >
                                                        {contributor.is_editor ? 'Remove Editor' : 'Make Editor'}
                                                    </button>
                                                    <button
                                                        onClick={() => removeContributor(contributor.contributor_id)}
                                                        className="bg-red-500 hover:bg-red-700 text-white text-sm py-1 px-3 rounded"
                                                    >
                                                        Remove
                                                    </button>
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