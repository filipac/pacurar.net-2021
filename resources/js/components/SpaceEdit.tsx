import React, {useEffect, useRef, useState} from 'react';

import {Editor} from '@tinymce/tinymce-react';
import {GqlClient} from "../graphql_client";
import {gql} from "graphql-request";
import {SpaceInfo} from "../utils/decodeSpaceInfo";
import MonacoEditor from "@monaco-editor/react";
import {useRecoilState} from "recoil/es/index.mjs";
import {tempAdAtom} from "../state/dapp";
import {Dialog, Transition} from "@headlessui/react";
import {Loader} from "@multiversx/sdk-dapp/UI";
import {Formik} from "formik";
import {object, string} from "yup";
import BigNumber from "bignumber.js";
import classnames from "classnames";
import {denominated} from "./BuySpace";

type Props = {
    spaceInfo: SpaceInfo

    sidebar: boolean

    close: () => void
    language: string

    refreshSpace: (html?: string) => void
}

type Model = {
    id: string
    name: string
    content: string
    last_modified_by: string
    created_at: string
    updated_at: string
}

const SpaceEdit: React.FC<Props> = ({spaceInfo, sidebar, close, language, refreshSpace}) => {
    const editorRef = useRef(null);

    const [editMode, setEditMode] = useState<"wysiwyg" | "code">('code');
    // const [tempValue, setTempValue] = useState<string>('');

    const [tempAd, setTempAd] = useRecoilState<{
        [key: string]: string
    }>(tempAdAtom);
    const tempValue = tempAd?.[spaceInfo.name] || '';
    const setTempValue = (value: string) => {
        setTempAd({
            ...tempAd,
            [spaceInfo.name]: value
        })
    }

    const [model, setModel] = useState<Model | null>(null);

    const save = async () => {
        let resp = await GqlClient.request(gql`mutation UpdateSpace($spaceName: String!, $content: String!) {
            updateSpace(spaceName: $spaceName, content: $content) {
                id
                name
                content
                last_modified_by
                created_at
                updated_at
            }
        }`, {
            spaceName: spaceInfo.name,
            content: tempValue
        })
        if (resp.updateSpace) {
            setModel(resp.updateSpace)
            refreshSpace(resp.updateSpace.content)
            close()
        }
    }

    const get = async () => {
        let resp = await GqlClient.request(gql`query GetSpace($spaceName: String!) {
            space(spaceName: $spaceName) {
                id
                name
                content
                last_modified_by
                created_at
                updated_at
            }
        }`, {
            spaceName: spaceInfo.name
        })
        setModel(resp.space)
    }

    useEffect(() => {
        get()

        return () => {
            setTempAd({
                ...tempAd,
                [spaceInfo.name]: undefined
            })
        }
    }, [spaceInfo.name])

    useEffect(() => {
        if (model && editorRef.current) {
            editorRef.current.setContent(model.content)
        }
        if (model) {
            setTempValue(model.content)
        }
    }, [model, editorRef])

    const content = (<>
            {/* html or wysiwyg chooser */}
            <div className="flex flex-row justify-center mt-4 mb-4">
                <button
                    className={`px-4 py-2 rounded-l-md ${editMode === 'wysiwyg' ? 'bg-gray-400' : 'bg-gray-100'}`}
                    onClick={() => setEditMode('wysiwyg')}
                >
                    WYSIWYG
                </button>
                <button
                    className={`px-4 py-2 rounded-r-md ${editMode === 'code' ? 'bg-gray-400' : 'bg-gray-100'}`}
                    onClick={() => setEditMode('code')}
                >
                    Code
                </button>
            </div>
            {editMode === 'wysiwyg' && <Editor
                tinymceScriptSrc={window.location.origin + '/tinymce/tinymce.min.js'}
                onInit={(evt, editor) => editorRef.current = editor}
                initialValue={tempValue || ''}
                onChange={(content, editor) => {
                    setTempValue(content.target.getContent())
                }}
                init={{
                    height: 500,
                    menubar: false,
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                    ],
                    toolbar: 'undo redo | blocks | ' +
                        'bold italic forecolor | alignleft aligncenter ' +
                        'alignright alignjustify | bullist numlist outdent indent | ' +
                        'removeformat | help',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
                }}
            />}
            {editMode === 'code' && <MonacoEditor
                height="30vh"
                language="html"
                theme="vs-dark"
                value={tempValue || ''}
                options={{
                    minimap: {
                        enabled: false
                    }
                }}
                onChange={(value, event) => {
                    setTempValue(value)
                }}
            />}
            <div className={classnames('mt-3 self-end flex gap-2 justify-end', {
                'flex-col mt-4': sidebar,
            })}>
                <button className={'p-2 bg-green-600 shadow-box hover:shadow-boxhvr text-lg text-black'}
                        onClick={e => {
                            e.preventDefault()
                            save()
                        }}>
                    {language == 'en' && <>Save</>}
                    {language == 'ro' && <>Salvează</>}
                </button>
            </div>
        </>
    )

    return sidebar ? <>
        <Transition.Root show as={'div'}>
            <Dialog
                as="div"
                className="fixed z-10 inset-0 overflow-y-auto"
                open
                onClose={(truth) => {
                    close()
                }}
            >
                <div
                    className="flex items-start justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                    <Transition.Child
                        as={React.Fragment}
                        enter="ease-out duration-300"
                        enterFrom="opacity-0"
                        enterTo="opacity-100"
                        leave="ease-in duration-200"
                        leaveFrom="opacity-100"
                        leaveTo="opacity-0"
                    >
                        <Dialog.Overlay
                            className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"/>
                    </Transition.Child>

                    {/* This element is to trick the browser into centering the modal contents. */}
                    <span className="hidden sm:inline-block sm:align-middle sm:h-screen"
                          aria-hidden="true">
            &#8203;
          </span>
                    <Transition.Child
                        as={React.Fragment}
                        enter="ease-out duration-300"
                        enterFrom="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enterTo="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leaveFrom="opacity-100 translate-y-0 sm:scale-100"
                        leaveTo="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <div
                            className="
                            inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left shadow-xl transform
                            transition-all sm:mt-32 sm:align-middle sm:w-full sm:p-6 relative
                            md:max-w-md lg:max-w-lg xl:max-w-2xl
                            self-end mb-12
                            ">
                            {content}
                        </div>
                    </Transition.Child>
                </div>
            </Dialog>
        </Transition.Root>
    </> : content
}

export default SpaceEdit
