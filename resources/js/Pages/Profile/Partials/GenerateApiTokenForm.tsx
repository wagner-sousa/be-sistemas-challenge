import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { router, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

export default function GenerateApiTokenForm({
    className = '',
    plainTextToken,
    tokens,
}: {
    className?: string;
    plainTextToken?: string;
    tokens: Array<{
        id: number;
        name: string;
        created_at: string;
        last_used_at?: string | null;
    }>;
}) {
    const [copied, setCopied] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        setCopied(false);

        post(route('profile.api-tokens.store'), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    const copyToken = async () => {
        if (!plainTextToken) {
            return;
        }

        try {
            await navigator.clipboard.writeText(plainTextToken);
            setCopied(true);
        } catch {
            setCopied(false);
        }
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                    API Token
                </h2>

                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Generate a new Sanctum token for authenticating API requests.
                </p>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-6">
                <div>
                    <InputLabel htmlFor="token_name" value="Token Name" />

                    <TextInput
                        id="token_name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        type="text"
                        className="mt-1 block w-full"
                        placeholder="Ex: Mobile client"
                        required
                    />

                    <InputError className="mt-2" message={errors.name} />
                </div>

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>Generate Token</PrimaryButton>

                    {plainTextToken && (
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            A new token is shown below.
                        </p>
                    )}
                </div>
            </form>

            {plainTextToken && (
                <div className="mt-6 space-y-3 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                    <div>
                        <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
                            Your new API token
                        </p>
                        <p className="text-xs text-gray-600 dark:text-gray-400">
                            Copy and store this token securely; it will not be shown again.
                        </p>
                    </div>

                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <TextInput
                            value={plainTextToken}
                            readOnly
                            className="w-full bg-white dark:bg-gray-800"
                        />
                        <SecondaryButton type="button" onClick={copyToken} disabled={!plainTextToken}>
                            {copied ? 'Copied' : 'Copy'}
                        </SecondaryButton>
                    </div>
                </div>
            )}

            <div className="mt-8 space-y-4">
                <div>
                    <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
                        Existing tokens
                    </p>
                    <p className="text-xs text-gray-600 dark:text-gray-400">
                        Revoke any tokens you no longer use.
                    </p>
                </div>

                {tokens.length === 0 ? (
                    <p className="text-sm text-gray-600 dark:text-gray-400">
                        No tokens yet.
                    </p>
                ) : (
                    <ul className="space-y-3">
                        {tokens.map((token) => (
                            <li
                                key={token.id}
                                className="flex flex-col gap-3 rounded-lg border border-gray-200 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/40 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div className="space-y-1">
                                    <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {token.name}
                                    </p>
                                    <p className="text-xs text-gray-600 dark:text-gray-400">
                                        Created: {new Date(token.created_at).toLocaleString()}
                                    </p>
                                    <p className="text-xs text-gray-600 dark:text-gray-400">
                                        Last used: {token.last_used_at ? new Date(token.last_used_at).toLocaleString() : 'Never'}
                                    </p>
                                </div>

                                <DangerButton
                                    type="button"
                                    onClick={() =>
                                        router.delete(route('profile.api-tokens.destroy', token.id), {
                                            preserveScroll: true,
                                        })
                                    }
                                >
                                    Delete
                                </DangerButton>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </section>
    );
}
