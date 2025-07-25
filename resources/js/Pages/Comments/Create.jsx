import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

export default function Create({ auth, task, project }) {
    const { data, setData, post, processing, errors } = useForm({
        task_id: task.id,
        name: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('comments.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Add Comment</h2>}
        >
            <Head title="Add Comment" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="mb-6">
                                <h3 className="text-lg font-semibold mb-2">Add Comment to Task</h3>
                                <p className="text-sm text-gray-600">Task: {task.name}</p>
                                <p className="text-sm text-gray-600">Project: {project.name}</p>
                            </div>

                            <form onSubmit={submit} className="max-w-md">
                                <div className="mb-4">
                                    <InputLabel htmlFor="name" value="Comment" />
                                    <textarea
                                        id="name"
                                        name="name"
                                        value={data.name}
                                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        rows="4"
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        placeholder="Write your comment here..."
                                    />
                                    <InputError message={errors.name} className="mt-2" />
                                </div>

                                <div className="flex items-center justify-end mt-4">
                                    <PrimaryButton className="ml-4" disabled={processing}>
                                        Add Comment
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 